<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Идемпотентность, изоляция, версии и владелец данных в синке.
 *
 * Задача 3.5: «повторная отправка того же батча не создаёт дублей».
 * Задача 3.8: сервер — источник версии записи (`updated_at`), клиент её применяет
 * локально (last-write-wins); время отдаётся в одном стандарте — ISO-8601 UTC.
 * Задача 3.9: удаления доезжают до других устройств (soft-delete/tombstones).
 * Задача 3.10: `/sync` и `/sync-updates` под `auth:sanctum`, у данных есть владелец.
 * Задача 3.12: деньги — целые рубли (integer-колонки, без `CAST` в запросах).
 *
 * Дополнительно проверяем контракт, на который опирается клиент:
 *   • каждая операция получает **явный** ответ (`synced`) — клиент считает
 *     операцию доставленной только по нему (FE-половина 3.5);
 *   • битая операция не срывает остальной батч (SAVEPOINT-изоляция из Go, 3.11);
 *   • `order_service` (без PK) дедуплицируется по натуральному ключу
 *     `order_id + service_id` и удаляется по нему же;
 *   • `server_id`/`*_server_id` из payload не попадают в реальные колонки;
 *   • у каждой подтверждённой операции есть `updated_at` — та же версия, что
 *     записана в БД (задача 3.8).
 *
 * Тест выполняется на **отдельной** тестовой БД (см. `phpunit.xml`):
 * `RefreshDatabase` сносит таблицы, поэтому на «не тестовой» БД тест
 * пропускается (setUp).
 */
class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

    /** Пользователь-владелец данных «по умолчанию» для теста. */
    private User $user;

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || !str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты SyncController запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();

        // Синк — под `auth:sanctum` (задача 3.10): без пользователя 401.
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Второй пользователь — для проверки изоляции данных (задача 3.10).
     */
    private function anotherUser(): User
    {
        return User::factory()->create();
    }

    /**
     * @param array<int, array<string, mixed>> $operations
     * @return array{synced: array, errors: array}
     */
    private function sync(array $operations, string $syncId = 'test-device'): array
    {
        $response = $this->postJson('/api/sync', ['operations' => $operations], ['X-Sync-ID' => $syncId]);
        $response->assertOk();

        return [
            'synced' => $response->json('synced') ?? [],
            'errors' => $response->json('errors') ?? [],
        ];
    }

    /**
     * @return array{count: int, records: array}
     */
    private function fetchUpdates(string $table, ?string $syncId = 'test-device', int $since = 0): array
    {
        $headers = $syncId === null ? [] : ['X-Sync-ID' => $syncId];

        $response = $this->getJson("/api/sync-updates?table={$table}&since={$since}", $headers);
        $response->assertOk();

        return [
            'count'   => (int) $response->json('count'),
            'records' => $response->json('records') ?? [],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function insertOp(string $table, string $localId, array $payload = []): array
    {
        return [
            'id'      => 'op-'.$localId,
            'type'    => 'insert',
            'table'   => $table,
            'payload' => array_merge(['local_id' => $localId], $payload),
        ];
    }

    /**
     * @return array{0: int, 1: int} [specialization_id, client_id]
     */
    private function seedOrderDeps(): array
    {
        $specializationId = DB::table('specializations')->insertGetId([
            'specializationName' => 'Тестовая специализация',
            'popularCounter'     => 0,
            // Владелец данных (задача 3.10): записи принадлежат пользователю.
            'user_id'            => $this->user->id,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $clientId = DB::table('clients')->insertGetId([
            'name'              => 'Клиент',
            'phone'             => '',
            'specialization_id' => $specializationId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return [$specializationId, $clientId];
    }

    private function seedOrder(): int
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();

        return DB::table('orders')->insertGetId([
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 1000,
            'user_id'           => $this->user->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function test_repeated_insert_does_not_create_duplicates(): void
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();
        $localId = '11111111-1111-1111-1111-111111111111';

        $operation = $this->insertOp('orders', $localId, [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'hours'             => 1,
            'minutes'           => 30,
            'total_amount'      => 1500,
        ]);

        $first = $this->sync([$operation]);
        $this->assertSame([], $first['errors']);
        $this->assertCount(1, $first['synced']);

        $serverId = $first['synced'][0]['server_id'];
        $this->assertNotNull($serverId);

        // Повторная отправка того же батча (например, ответ сервера потерялся).
        $second = $this->sync([$operation]);
        $this->assertSame([], $second['errors']);
        $this->assertSame($serverId, $second['synced'][0]['server_id']);

        $this->assertSame(1, DB::table('orders')->count(), 'Повторная отправка не должна создавать дубль');
        $this->assertSame($localId, DB::table('orders')->where('id', $serverId)->value('uuid_id'));
    }

    public function test_repeated_insert_updates_fields_instead_of_duplicating(): void
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();
        $localId = '22222222-2222-2222-2222-222222222222';

        $this->sync([$this->insertOp('orders', $localId, [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 100,
        ])]);

        $this->sync([$this->insertOp('orders', $localId, [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 777,
        ])]);

        $this->assertSame(1, DB::table('orders')->count());
        $this->assertSame(777, (int) DB::table('orders')->where('uuid_id', $localId)->value('total_amount'));
    }

    public function test_order_without_client_is_accepted(): void
    {
        // Заказ без клиента — штатный офлайн-сценарий: заказ заводят «на приёмке»,
        // а клиента вписывают позже (в клиенте `client_id` необязательное поле).
        // Было: `orders.client_id` NOT NULL → `/sync` отвечал DATABASE_ERROR
        // (not-null violation) и заказ навсегда «залипал» в очереди устройства.
        [$specializationId] = $this->seedOrderDeps();
        $localId = '44444444-4444-4444-4444-444444444444';

        $result = $this->sync([$this->insertOp('orders', $localId, [
            'specialization_id' => $specializationId,
            'client_id'         => null,
            'total_amount'      => 0,
        ])]);

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['synced']);

        $row = DB::table('orders')->where('uuid_id', $localId)->first();

        $this->assertNotNull($row, 'Заказ без клиента должен попасть в БД');
        $this->assertNull($row->client_id);
    }

    public function test_order_client_can_be_cleared_by_update(): void
    {
        // Обратный сценарий: клиента привязали, потом убрали — правка должна доехать
        // (UPDATE с `client_id: null`), иначе на сервере остался бы старый клиент.
        [$specializationId, $clientId] = $this->seedOrderDeps();
        $localId = '55555555-5555-5555-5555-555555555555';

        $insert = $this->sync([$this->insertOp('orders', $localId, [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 500,
        ])]);
        $serverId = $insert['synced'][0]['server_id'];

        $update = $this->sync([[
            'id'      => 'op-'.$localId,
            'type'    => 'update',
            'table'   => 'orders',
            'payload' => [
                'id'                => $serverId,
                'local_id'          => $localId,
                'specialization_id' => $specializationId,
                'client_id'         => null,
                'total_amount'      => 500,
            ],
        ]]);

        $this->assertSame([], $update['errors']);
        $this->assertNull(DB::table('orders')->where('id', $serverId)->value('client_id'));
    }

    public function test_generic_table_insert_is_idempotent_by_uuid_id(): void
    {
        $localId = '33333333-3333-3333-3333-333333333333';
        $operation = $this->insertOp('clients', $localId, ['name' => 'Иван', 'phone' => '+7']);

        $first = $this->sync([$operation]);
        $second = $this->sync([$operation]);

        $this->assertSame([], $first['errors']);
        $this->assertSame([], $second['errors']);
        $this->assertSame($first['synced'][0]['server_id'], $second['synced'][0]['server_id']);
        $this->assertSame(1, DB::table('clients')->where('uuid_id', $localId)->count());
    }

    public function test_order_service_is_deduplicated_by_natural_key(): void
    {
        $orderId = $this->seedOrder();
        $serviceId = DB::table('services')->insertGetId([
            'service'    => 'Работа',
            'price'      => '1000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $localId = '44444444-4444-4444-4444-444444444444';
        $operation = $this->insertOp('order_service', $localId, [
            'order_id'   => $orderId,
            'service_id' => $serviceId,
            'sale_price' => 900,
            'quantity'   => 2,
        ]);

        $first = $this->sync([$operation]);
        $second = $this->sync([$operation]);

        $this->assertSame([], $first['errors']);
        $this->assertSame([], $second['errors']);
        $this->assertSame(1, DB::table('order_service')->count(), 'У связки не должно быть дублей');
        $this->assertSame($localId, DB::table('order_service')->value('uuid_id'));
        $this->assertSame(2, (int) DB::table('order_service')->value('quantity'));
    }

    public function test_order_service_is_deleted_by_natural_key(): void
    {
        $orderId = $this->seedOrder();
        $serviceId = DB::table('services')->insertGetId([
            'service'    => 'Работа',
            'price'      => '1000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_service')->insert([
            'order_id'   => $orderId,
            'service_id' => $serviceId,
            'sale_price' => 1000,
            'quantity'   => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->sync([[
            'id'      => 'op-del-order-service',
            'type'    => 'delete',
            'table'   => 'order_service',
            'payload' => [
                'local_id'   => '55555555-5555-5555-5555-555555555555',
                'order_id'   => $orderId,
                'service_id' => $serviceId,
            ],
        ]]);

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['synced']);

        // У `order_service` есть `deleted_at`, поэтому удаление — soft (задача 3.9):
        // «живой» строки нет, но остался tombstone для других устройств.
        $this->assertSame(0, DB::table('order_service')->whereNull('deleted_at')->count());
        $this->assertNotNull(
            DB::table('order_service')->where('order_id', $orderId)->where('service_id', $serviceId)->value('deleted_at')
        );
    }

    public function test_broken_operation_does_not_break_the_batch(): void
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();

        $bad = $this->insertOp('clients', '77777777-7777-7777-7777-777777777777', [
            'name'               => 'Плохая операция',
            'nonexistent_column' => 'нет такой колонки',
        ]);

        $good = $this->insertOp('orders', '88888888-8888-8888-8888-888888888888', [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 10,
        ]);

        $result = $this->sync([$bad, $good]);

        $this->assertCount(1, $result['errors'], 'Битая операция должна вернуть ошибку');
        $this->assertSame('77777777-7777-7777-7777-777777777777', $result['errors'][0]['local_id']);
        $this->assertSame('DATABASE_ERROR', $result['errors'][0]['error']);

        $this->assertCount(1, $result['synced'], 'Вторая операция батча должна примениться');
        $this->assertSame('88888888-8888-8888-8888-888888888888', $result['synced'][0]['local_id']);

        $this->assertSame(0, DB::table('clients')->where('uuid_id', '77777777-7777-7777-7777-777777777777')->count());
        $this->assertSame(1, DB::table('orders')->where('uuid_id', '88888888-8888-8888-8888-888888888888')->count());
    }

    public function test_update_is_acknowledged_even_when_nothing_changed(): void
    {
        $orderId = $this->seedOrder();

        $operation = [
            'id'      => 'op-update',
            'type'    => 'update',
            'table'   => 'orders',
            'payload' => ['id' => $orderId, 'comments' => 'комментарий'],
        ];

        $first = $this->sync([$operation]);
        $this->assertSame([], $first['errors']);
        $this->assertSame($orderId, $first['synced'][0]['server_id']);

        // Повтор того же UPDATE: affected = 0, но ответ по операции всё равно есть —
        // иначе клиент считал бы операцию недоставленной и повторял её вечно.
        $second = $this->sync([$operation]);
        $this->assertSame([], $second['errors']);
        $this->assertCount(1, $second['synced']);
        $this->assertSame($orderId, $second['synced'][0]['server_id']);
    }

    public function test_update_of_missing_record_returns_error(): void
    {
        $result = $this->sync([[
            'id'      => 'op-update-missing',
            'type'    => 'update',
            'table'   => 'orders',
            'payload' => ['id' => 999999999, 'comments' => 'x'],
        ]]);

        $this->assertSame([], $result['synced']);
        $this->assertCount(1, $result['errors']);
        $this->assertSame('RECORD_NOT_FOUND', $result['errors'][0]['error']);
    }

    public function test_delete_is_idempotent(): void
    {
        $orderId = $this->seedOrder();

        $operation = [
            'id'      => 'op-delete',
            'type'    => 'delete',
            'table'   => 'orders',
            'payload' => ['id' => $orderId],
        ];

        $first = $this->sync([$operation]);
        $second = $this->sync([$operation]);

        $this->assertSame([], $first['errors']);
        $this->assertSame([], $second['errors']);

        // У `orders` есть `deleted_at`, значит удаление — soft (задача 3.9):
        // строка остаётся как tombstone, но «живых» заказов с таким id нет.
        $this->assertSame(0, DB::table('orders')->where('id', $orderId)->whereNull('deleted_at')->count());
        $this->assertNotNull(DB::table('orders')->where('id', $orderId)->value('deleted_at'));
    }

    public function test_server_id_fields_are_stripped_from_payload(): void
    {
        // Клиент может прислать в payload своё `server_id`/`*_server_id`
        // (например, при повторной отправке) — на сервере таких колонок нет,
        // и без вырезания операция падала бы (перенос из Go, D1/3.11).
        $result = $this->sync([$this->insertOp('clients', '66666666-6666-6666-6666-666666666666', [
            'name'                     => 'Клиент с server_id',
            'phone'                    => '',
            'server_id'                => 999,
            'specialization_server_id' => 777,
        ])]);

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, DB::table('clients')->where('uuid_id', '66666666-6666-6666-6666-666666666666')->count());
    }

    public function test_own_changes_are_not_echoed_back_to_the_device(): void
    {
        $localId = '99999999-9999-9999-9999-999999999999';

        $this->sync([$this->insertOp('specializations', $localId, [
            'specializationName' => 'Мастерская',
            'popularCounter'     => 0,
        ])], 'device-a');

        // Автор изменения не получает своё же изменение обратно (анти-эхо, 3.6).
        $this->assertSame(0, $this->fetchUpdates('specializations', 'device-a')['count']);

        // Другое устройство запись получает.
        $other = $this->fetchUpdates('specializations', 'device-b');
        $this->assertSame(1, $other['count']);
        $this->assertSame($localId, $other['records'][0]['uuid_id']);

        // Без X-Sync-ID фильтра нет — отдаём всё.
        $this->assertSame(1, $this->fetchUpdates('specializations', null)['count']);
    }

    public function test_change_by_another_device_comes_back_to_the_author(): void
    {
        $localId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';

        $this->sync([$this->insertOp('specializations', $localId, [
            'specializationName' => 'Мастерская',
            'popularCounter'     => 0,
        ])], 'device-a');

        $specializationId = DB::table('specializations')->where('uuid_id', $localId)->value('id');

        // Другое устройство правит запись — теперь она снова нужна автору.
        $this->sync([[
            'id'      => 'op-update-spec',
            'type'    => 'update',
            'table'   => 'specializations',
            'payload' => ['id' => $specializationId, 'specializationName' => 'Мастерская-2'],
        ]], 'device-b');

        $own = $this->fetchUpdates('specializations', 'device-a');
        $this->assertSame(1, $own['count']);
        $this->assertSame('Мастерская-2', $own['records'][0]['specializationName']);
        $this->assertSame('device-b', $own['records'][0]['last_sync_id']);

        // А устройство, которое правило, — не получает: это его изменение.
        $this->assertSame(0, $this->fetchUpdates('specializations', 'device-b')['count']);
    }

    public function test_order_service_insert_is_not_echoed_to_the_same_device(): void
    {
        $orderId = $this->seedOrder();
        $serviceId = DB::table('services')->insertGetId([
            'service'    => 'Работа',
            'price'      => '1000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->sync([$this->insertOp('order_service', 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', [
            'order_id'   => $orderId,
            'service_id' => $serviceId,
            'sale_price' => 900,
            'quantity'   => 1,
        ])], 'device-a');

        $this->assertSame(0, $this->fetchUpdates('order_service', 'device-a')['count']);
        $this->assertSame(1, $this->fetchUpdates('order_service', 'device-b')['count']);
    }

    /**
     * Проверяет, что значение — ISO-8601 UTC (`2026-09-12T10:00:00.000000Z`).
     * Единый стандарт времени для обоих роутов синка (задача 3.8).
     */
    private function assertIsoUtc(string $value): void
    {
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?Z$/',
            $value,
            "Ожидался ISO-8601 UTC timestamp, получено: {$value}"
        );
    }

    /**
     * Версия из ответа должна совпадать с тем, что реально записано в БД:
     * клиент сохранит её локально, иначе его «своя» версия разошлась бы с сервером.
     */
    private function assertMatchesStored(string $table, int $serverId, string $returned): void
    {
        $this->assertSame(
            Carbon::parse(DB::table($table)->where('id', $serverId)->value('updated_at'))->toJSON(),
            $returned,
            "`updated_at` из ответа /sync не совпал с версией в таблице {$table}"
        );
    }

    public function test_insert_response_contains_server_updated_at(): void
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();

        // orders — отдельная ветка insert (маппинг колонок)
        $orders = $this->sync([$this->insertOp('orders', '11111111-0000-0000-0000-000000000001', [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 1500,
        ])]);

        $this->assertSame([], $orders['errors']);
        $this->assertIsoUtc($orders['synced'][0]['updated_at']);
        $this->assertMatchesStored('orders', $orders['synced'][0]['server_id'], $orders['synced'][0]['updated_at']);

        // generic-путь (clients)
        $clients = $this->sync([$this->insertOp('clients', '11111111-0000-0000-0000-000000000002', [
            'name'  => 'Иван',
            'phone' => '+7',
        ])]);

        $this->assertSame([], $clients['errors']);
        $this->assertIsoUtc($clients['synced'][0]['updated_at']);
        $this->assertMatchesStored('clients', $clients['synced'][0]['server_id'], $clients['synced'][0]['updated_at']);
    }

    public function test_order_service_insert_response_contains_server_updated_at(): void
    {
        $orderId = $this->seedOrder();
        $serviceId = DB::table('services')->insertGetId([
            'service'    => 'Работа',
            'price'      => '1000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->sync([$this->insertOp('order_service', '22222222-0000-0000-0000-000000000001', [
            'order_id'   => $orderId,
            'service_id' => $serviceId,
            'sale_price' => 900,
            'quantity'   => 1,
        ])]);

        $this->assertSame([], $result['errors']);
        $this->assertIsoUtc($result['synced'][0]['updated_at']);

        // У связки нет своего PK — версию сверяем по натуральному ключу.
        $this->assertSame(
            Carbon::parse(
                DB::table('order_service')
                    ->where('order_id', $orderId)
                    ->where('service_id', $serviceId)
                    ->value('updated_at')
            )->toJSON(),
            $result['synced'][0]['updated_at']
        );
    }

    public function test_update_response_contains_server_updated_at(): void
    {
        $orderId = $this->seedOrder();

        $result = $this->sync([[
            'id'      => 'op-update-stamp',
            'type'    => 'update',
            'table'   => 'orders',
            'payload' => ['id' => $orderId, 'comments' => 'комментарий'],
        ]]);

        $this->assertSame([], $result['errors']);
        $this->assertSame($orderId, $result['synced'][0]['server_id']);
        $this->assertIsoUtc($result['synced'][0]['updated_at']);
        $this->assertMatchesStored('orders', $orderId, $result['synced'][0]['updated_at']);
    }

    public function test_soft_delete_bumps_version_and_returns_stamp(): void
    {
        $localId = '33333333-0000-0000-0000-000000000001';

        $this->sync([$this->insertOp('clients', $localId, ['name' => 'Иван'])], 'device-a');

        $clientId = DB::table('clients')->where('uuid_id', $localId)->value('id');
        $before = DB::table('clients')->where('id', $clientId)->value('updated_at');

        // Версия измеряется в секундах: сдвигаем время, чтобы «до» и «после» не совпали.
        $this->travel(2)->seconds();

        $result = $this->sync([[
            'id'      => 'op-delete-stamp',
            'type'    => 'delete',
            'table'   => 'clients',
            'payload' => ['id' => $clientId],
        ]], 'device-a');

        $this->assertSame([], $result['errors']);
        $this->assertNotNull(DB::table('clients')->where('id', $clientId)->value('deleted_at'));
        $this->assertIsoUtc($result['synced'][0]['updated_at']);
        $this->assertMatchesStored('clients', $clientId, $result['synced'][0]['updated_at']);

        // Soft-delete — изменение записи: версия обязана двинуться вперёд,
        // иначе удаление не «доедет» до другого устройства по курсору (задача 3.9).
        $after = DB::table('clients')->where('id', $clientId)->value('updated_at');
        $this->assertTrue(Carbon::parse($after)->greaterThan(Carbon::parse($before)));
    }

    public function test_fetch_updates_returns_iso_timestamps(): void
    {
        $localId = '44444444-0000-0000-0000-000000000001';

        $this->sync([$this->insertOp('clients', $localId, ['name' => 'Иван'])], 'device-a');

        $records = $this->fetchUpdates('clients', 'device-b')['records'];

        $this->assertCount(1, $records);
        // «Сырое» `2026-09-12 10:00:00` из Postgres клиентский `Date.parse`
        // принимает за локальное время устройства — версии и курсор «плыли» бы (3.8).
        $this->assertIsoUtc($records[0]['created_at']);
        $this->assertIsoUtc($records[0]['updated_at']);
    }



    /**
     * Задача 3.9: удаление заказа доезжает до другого устройства.
     * У `orders` есть `deleted_at`, поэтому удаление — soft, и выдача отдаёт
     * ту же строку с `deleted`/`deleted_at` (tombstone).
     */
    public function test_order_deletion_reaches_another_device(): void
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();
        $localId = '55555555-0000-0000-0000-000000000001';

        $created = $this->sync([$this->insertOp('orders', $localId, [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 100,
        ])], 'device-a');

        $orderId = $created['synced'][0]['server_id'];

        $this->sync([[
            'id'      => 'op-del-orders',
            'type'    => 'delete',
            'table'   => 'orders',
            'payload' => ['id' => $orderId],
        ]], 'device-a');

        // Автор удаления свой tombstone не получает (анти-эхо, 3.6).
        $this->assertSame(0, $this->fetchUpdates('orders', 'device-a')['count']);

        // Второе устройство узнаёт об удалении.
        $other = $this->fetchUpdates('orders', 'device-b');
        $this->assertSame(1, $other['count']);
        $this->assertTrue($other['records'][0]['deleted']);
        $this->assertNotNull($other['records'][0]['deleted_at']);
    }

    /**
     * Таблицы без `deleted_at` удаляются физически — их удаления лежат
     * в `sync_tombstones` и тоже доезжают до других устройств (задача 3.9).
     */
    public function test_hard_delete_is_returned_as_tombstone(): void
    {
        $orderId = $this->seedOrder();

        $created = $this->sync([$this->insertOp('materials', '66666666-0000-0000-0000-000000000001', [
            'order_id' => $orderId,
            'name'     => 'Клей',
            'price'    => 300,
            'amount'   => 1,
        ])], 'device-a');

        $materialId = $created['synced'][0]['server_id'];
        $this->assertSame(1, DB::table('materials')->where('id', $materialId)->count());

        $deleteOp = [
            'id'      => 'op-del-materials',
            'type'    => 'delete',
            'table'   => 'materials',
            'payload' => ['id' => $materialId],
        ];

        $this->sync([$deleteOp], 'device-a');

        // Строки на сервере больше нет...
        $this->assertSame(0, DB::table('materials')->where('id', $materialId)->count());

        // ...а другое устройство получает tombstone.
        $other = $this->fetchUpdates('materials', 'device-b');
        $this->assertSame(1, $other['count']);
        $this->assertSame($materialId, $other['records'][0]['id']);
        $this->assertTrue($other['records'][0]['deleted']);

        // Повторное удаление tombstone не дублирует (идемпотентность, 3.5).
        $second = $this->sync([$deleteOp], 'device-a');
        $this->assertSame([], $second['errors']);
        $this->assertSame(1, DB::table('sync_tombstones')->where('table_name', 'materials')->count());
        $this->assertSame(0, $this->fetchUpdates('materials', 'device-a')['count']);
    }


    /**
     * Связка `order_service` (натуральный ключ, нет своего PK): удаление —
     * soft, поэтому доезжает до другого устройства и несёт `uuid_id`, по
     * которому клиент находит свою локальную строку (задачи 3.5/3.9).
     */
    public function test_order_service_deletion_reaches_another_device_and_can_be_revived(): void
    {
        $orderId = $this->seedOrder();
        $serviceId = DB::table('services')->insertGetId([
            'service'    => 'Работа',
            'price'      => '1000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $lineUuid = '77777777-0000-0000-0000-000000000001';

        $insertOp = $this->insertOp('order_service', $lineUuid, [
            'order_id'   => $orderId,
            'service_id' => $serviceId,
            'sale_price' => 900,
            'quantity'   => 1,
        ]);

        $this->sync([$insertOp], 'device-a');
        $this->assertSame(1, $this->fetchUpdates('order_service', 'device-b')['count']);

        // Удаление по натуральному ключу — soft (у связки есть `deleted_at`).
        $this->sync([[
            'id'      => 'op-del-line',
            'type'    => 'delete',
            'table'   => 'order_service',
            'payload' => ['order_id' => $orderId, 'service_id' => $serviceId],
        ]], 'device-a');

        $deleted = $this->fetchUpdates('order_service', 'device-b');
        $this->assertSame(1, $deleted['count']);
        $this->assertTrue($deleted['records'][0]['deleted']);
        $this->assertSame($lineUuid, $deleted['records'][0]['uuid_id']);

        // Работу добавили снова — натуральный ключ тот же, строка «оживает».
        $this->sync([$this->insertOp('order_service', $lineUuid, [
            'order_id'   => $orderId,
            'service_id' => $serviceId,
            'sale_price' => 950,
            'quantity'   => 2,
        ])], 'device-a');

        $alive = $this->fetchUpdates('order_service', 'device-b');
        $this->assertSame(1, $alive['count']);
        $this->assertFalse($alive['records'][0]['deleted']);
        $this->assertSame(950, (int) $alive['records'][0]['sale_price']);
        $this->assertSame(2, (int) $alive['records'][0]['quantity']);
    }


    /**
     * Задача 3.12: деньги на сервере — целые рубли.
     * `services.price` был VARCHAR и кастовался в каждом расчёте (`CAST(... AS numeric)`);
     * теперь это integer, а payload синка нормализуется на входе.
     */
    public function test_service_price_is_integer_and_payload_is_normalized(): void
    {
        $column = DB::selectOne(
            "select data_type from information_schema.columns
             where table_schema = 'public' and table_name = 'services' and column_name = 'price'"
        );

        $this->assertSame('integer', $column->data_type, 'services.price должен быть целым числом');

        // Строка с разделителями и копейками: «1 500,50» → 1501 (целые рубли).
        $this->sync([$this->insertOp('services', '88888888-0000-0000-0000-000000000001', [
            'service' => 'Стрижка',
            'price'   => '1 500,50',
        ])], 'device-a');

        // Пустая строка у услуги — «цена не задана» (колонка NOT NULL → 0).
        $this->sync([$this->insertOp('services', '88888888-0000-0000-0000-000000000002', [
            'service' => 'Без цены',
            'price'   => '',
        ])], 'device-a');

        $this->assertSame(
            1501,
            (int) DB::table('services')->where('uuid_id', '88888888-0000-0000-0000-000000000001')->value('price')
        );
        $this->assertSame(
            0,
            (int) DB::table('services')->where('uuid_id', '88888888-0000-0000-0000-000000000002')->value('price')
        );

        // Update тоже нормализуется: строка вместо числа.
        $serviceId = DB::table('services')->where('uuid_id', '88888888-0000-0000-0000-000000000001')->value('id');
        $this->sync([[
            'id'      => 'op-update-price',
            'type'    => 'update',
            'table'   => 'services',
            'payload' => ['id' => $serviceId, 'price' => '2000'],
        ]], 'device-a');

        $this->assertSame(2000, (int) DB::table('services')->where('id', $serviceId)->value('price'));
    }

    /**
     * Задача 3.12 (продолжение): `sale_price` связки — всегда число, а статистика
     * считает по integer-колонкам без `CAST` (иначе запрос падал бы на новых типах).
     */
    public function test_order_service_sale_price_is_numeric_and_statistics_work(): void
    {
        $orderId = $this->seedOrder();
        // Берём специализацию именно этого заказа: `seedOrder()` создаёт свою.
        $specializationId = (int) DB::table('orders')->where('id', $orderId)->value('specialization_id');
        $serviceId = DB::table('services')->insertGetId([
            'service'    => 'Работа',
            'price'      => 1200,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->sync([$this->insertOp('order_service', '99999999-0000-0000-0000-000000000001', [
            'order_id'   => $orderId,
            'service_id' => $serviceId,
            'quantity'   => 1,
            // sale_price не передан → сервер берёт цену услуги
        ])], 'device-a');

        $this->assertSame([], $result['errors']);

        $salePrice = DB::table('order_service')
            ->where('order_id', $orderId)
            ->where('service_id', $serviceId)
            ->value('sale_price');

        $this->assertIsNumeric($salePrice, 'sale_price должен быть числом (без строк/кастов)');
        $this->assertSame(1200, (int) $salePrice);

        // Статистика (задача 3.12): суммы по integer-колонкам без CAST. С задачи 9.1
        // в выручку входят только закрытые и оплаченные заказы, поэтому «закрываем» заказ.
        DB::table('orders')->where('id', $orderId)->update(['status' => 'done', 'paid' => true]);

        $stats = app(\App\Repositories\StatisticRepository::class)->getProfitDWMY($specializationId);
        $this->assertNotEmpty($stats);
        $this->assertSame(1200, (int) $stats[0]->total_month);

        $topServices = app(\App\Repositories\StatisticRepository::class)->getTopServicesBySpecialization($specializationId);
        $this->assertNotEmpty($topServices);
        $this->assertSame('Работа', $topServices[0]->service);
        $this->assertSame(1200, (int) $topServices[0]->total);
    }


    /**
     * Задача 3.10: без токена синк недоступен — иначе данные остаются «ничьими»
     * и смешиваются между устройствами.
     */
    public function test_sync_requires_authentication(): void
    {
        // Снимаем «вход» из setUp: запросы уходят без пользователя.
        $this->app['auth']->forgetGuards();

        $this->postJson('/api/sync', ['operations' => []])->assertUnauthorized();
        $this->getJson('/api/sync-updates?table=clients&since=0')->assertUnauthorized();
    }

    /**
     * Задача 3.10: устройство видит только данные своего пользователя.
     */
    public function test_device_sees_only_its_owners_data(): void
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();

        $created = $this->sync([$this->insertOp('orders', 'aaaaaaaa-0000-0000-0000-00000000000a', [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 500,
        ])], 'device-a');

        $this->assertNotNull($created['synced'][0]['server_id']);

        // Владелец свои данные видит (без анти-эхо-заголовка).
        $this->assertSame(1, $this->fetchUpdates('orders', null)['count']);

        // Другому пользователю не отдаём ни заказ, ни клиента, ни специализацию.
        Sanctum::actingAs($this->anotherUser());

        $this->assertSame(0, $this->fetchUpdates('orders', null)['count']);
        $this->assertSame(0, $this->fetchUpdates('clients', null)['count']);
        $this->assertSame(0, $this->fetchUpdates('specializations', null)['count']);
    }

    /**
     * Задача 3.10: чужую запись нельзя ни обновить, ни удалить.
     */
    public function test_cannot_update_or_delete_foreign_record(): void
    {
        $orderId = $this->seedOrder();

        Sanctum::actingAs($this->anotherUser());

        $update = $this->sync([[
            'id'      => 'op-foreign-update',
            'type'    => 'update',
            'table'   => 'orders',
            'payload' => ['id' => $orderId, 'comments' => 'чужая правка'],
        ]]);

        $this->assertSame('RECORD_NOT_FOUND', $update['errors'][0]['error']);

        $delete = $this->sync([[
            'id'      => 'op-foreign-delete',
            'type'    => 'delete',
            'table'   => 'orders',
            'payload' => ['id' => $orderId],
        ]]);

        $this->assertSame('RECORD_NOT_FOUND', $delete['errors'][0]['error']);

        $order = DB::table('orders')->where('id', $orderId)->first();
        $this->assertNull($order->deleted_at, 'Чужой заказ не должен быть удалён');
        $this->assertNotSame('чужая правка', $order->comments);
    }

    /**
     * Задача 3.10: нельзя привязать свою запись к чужому родителю.
     */
    public function test_cannot_insert_child_into_foreign_parent(): void
    {
        $orderId = $this->seedOrder(); // заказ пользователя A
        $serviceId = DB::table('services')->insertGetId([
            'service'    => 'Работа',
            'price'      => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($this->anotherUser());

        $result = $this->sync([$this->insertOp('order_service', 'bbbbbbbb-0000-0000-0000-00000000000b', [
            'order_id'   => $orderId,
            'service_id' => $serviceId,
            'quantity'   => 1,
        ])]);

        $this->assertSame('FORBIDDEN_NOT_OWNER', $result['errors'][0]['error']);
        $this->assertSame(0, DB::table('order_service')->where('order_id', $orderId)->count());
    }

    /**
     * Задача 3.10: `user_id` заказа больше не теряется при вставке из синка.
     */
    public function test_order_insert_sets_owner(): void
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();

        $created = $this->sync([$this->insertOp('orders', 'cccccccc-0000-0000-0000-00000000000c', [
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 100,
        ])], 'device-a');

        $orderId = $created['synced'][0]['server_id'];

        $this->assertSame(
            $this->user->id,
            (int) DB::table('orders')->where('id', $orderId)->value('user_id'),
            'Заказ из синка должен принадлежать пользователю из токена'
        );
    }

    /**
     * Порядок «родитель → ребёнок» (задача 5.6).
     *
     * Сервер не переупорядочивает операции в батче и не создаёт родителя
     * автоматически: `server_id` появляется только в ответе, поэтому клиент
     * отправляет родителя раньше ребёнка и дожимает ребёнка следующей «волной»
     * уже с серверным id (`syncService` — топосортировка + волны, задача 3.2).
     *
     * Здесь проверяется контракт, на который опирается эта схема:
     *   • id из ответа пригоден как FK для следующего запроса;
     *   • ребёнок без родителя (отправленный «слишком рано») отвергается
     *     и не пишется в БД.
     */
    public function test_parent_id_is_usable_by_child_and_orphan_is_rejected(): void
    {
        // Волна 1 — родитель (специализация).
        $parent = $this->sync([$this->insertOp('specializations', 'aaaaaaaa-0000-0000-0000-00000000000a', [
            'specializationName' => 'Ремонт',
            'popularCounter'     => 0,
        ])], 'device-a');

        $this->assertSame([], $parent['errors']);
        $specializationId = $parent['synced'][0]['server_id'];
        $this->assertNotNull($specializationId);

        // Волна 2 — ребёнок (категория) со серверным id родителя.
        $child = $this->sync([$this->insertOp('categories', 'bbbbbbbb-0000-0000-0000-00000000000b', [
            'category_name'     => 'Электрика',
            'specialization_id' => $specializationId,
        ])], 'device-a');

        $this->assertSame([], $child['errors']);
        $this->assertSame(
            $specializationId,
            (int) DB::table('categories')
                ->where('uuid_id', 'bbbbbbbb-0000-0000-0000-00000000000b')
                ->value('specialization_id'),
            'Ребёнок должен ссылаться на серверный id родителя'
        );

        // Ребёнок без родителя: сервер не создаёт родителя сам — операция отвергается.
        $orphan = $this->sync([$this->insertOp('categories', 'dddddddd-0000-0000-0000-00000000000d', [
            'category_name'     => 'Осиротевшая',
            'specialization_id' => 99999999,
        ])], 'device-a');
        $this->assertCount(1, $orphan['errors']);
        // Существование чужой/несуществующей записи сервер не подтверждает,
        // поэтому ответ — тот же, что и на чужого родителя (задача 3.10).
        $this->assertSame('FORBIDDEN_NOT_OWNER', $orphan['errors'][0]['error']);
        $this->assertSame(
            0,
            DB::table('categories')->where('uuid_id', 'dddddddd-0000-0000-0000-00000000000d')->count(),
            'Ребёнок без родителя не должен попасть в БД'
        );
    }

    /**
     * Задачи 9.5/9.6: себестоимость товара в позиции заказа.
     *
     * Клиент присылает `buy_price` (закупка на момент продажи) — деньги нормализуются как
     * везде (строка «250,60» → 251). Если поля нет (web-форма, старый клиент), сервер берёт
     * последнюю закупку товара (`buy_product_prices`, иначе последний приход), а при
     * отсутствии истории оставляет `NULL` — «закупка неизвестна», а не «закупка 0».
     */
    public function test_order_product_buy_price_is_normalized_and_taken_from_last_purchase(): void
    {
        [$specializationId] = $this->seedOrderDeps();

        $orderId = DB::table('orders')->insertGetId([
            'specialization_id' => $specializationId,
            'client_id'         => DB::table('clients')->where('specialization_id', $specializationId)->value('id'),
            'total_amount'      => 0,
            'user_id'           => $this->user->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $productCategoryId = DB::table('product_categories')->insertGetId([
            'name'              => 'Фильтры',
            'specialization_id' => $specializationId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $productWithHistory = DB::table('products')->insertGetId([
            'name'                => 'Фильтр',
            'product_category_id' => $productCategoryId,
            'base_sale_price'     => 1000,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $productWithoutHistory = DB::table('products')->insertGetId([
            'name'                => 'Шланг',
            'product_category_id' => $productCategoryId,
            'base_sale_price'     => 500,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // История закупок: её пишет приход (задача 9.2).
        DB::table('buy_product_prices')->insert([
            'product_id' => $productWithHistory,
            'buy_price'  => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->sync([
            // 1) закупку прислал клиент, строкой с разделителями
            $this->insertOp('order_product', 'cccccccc-0000-0000-0000-000000000001', [
                'order_id'   => $orderId,
                'product_id' => $productWithHistory,
                'sale_price' => 1000,
                'quantity'   => 1,
                'buy_price'  => '250,60',
            ]),
            // 2) закупки в payload нет — берём последнюю из истории
            $this->insertOp('order_product', 'cccccccc-0000-0000-0000-000000000002', [
                'order_id'   => $orderId,
                'product_id' => $productWithHistory,
                'sale_price' => 1000,
                'quantity'   => 2,
            ]),
            // 3) истории закупок нет — остаётся NULL
            $this->insertOp('order_product', 'cccccccc-0000-0000-0000-000000000003', [
                'order_id'   => $orderId,
                'product_id' => $productWithoutHistory,
                'sale_price' => 500,
                'quantity'   => 1,
            ]),
        ], 'device-a');

        $this->assertSame([], $result['errors']);

        $this->assertSame(
            251,
            (int) DB::table('order_product')->where('uuid_id', 'cccccccc-0000-0000-0000-000000000001')->value('buy_price')
        );
        $this->assertSame(
            700,
            (int) DB::table('order_product')->where('uuid_id', 'cccccccc-0000-0000-0000-000000000002')->value('buy_price'),
            'Без buy_price в payload сервер подставляет последнюю закупку товара'
        );
        $this->assertNull(
            DB::table('order_product')->where('uuid_id', 'cccccccc-0000-0000-0000-000000000003')->value('buy_price'),
            'Без истории закупок себестоимость остаётся неизвестной (NULL), а не 0'
        );
    }

    /**
     * Задачи 9.5/9.6: у ручной позиции (`materials`) источник закупки один — форма,
     * поэтому сервер её просто сохраняет (и не выдумывает, если её не прислали).
     */
    public function test_materials_buy_price_is_synced_from_the_client(): void
    {
        $orderId = $this->seedOrder();

        $result = $this->sync([
            $this->insertOp('materials', 'eeeeeeee-0000-0000-0000-000000000001', [
                'order_id'  => $orderId,
                'name'      => 'Герметик',
                'price'     => 200,
                'amount'    => 2,
                'buy_price' => 50,
            ]),
            $this->insertOp('materials', 'eeeeeeee-0000-0000-0000-000000000002', [
                'order_id' => $orderId,
                'name'     => 'Скотч',
                'price'    => 100,
                'amount'   => 1,
            ]),
        ], 'device-a');

        $this->assertSame([], $result['errors']);
        $this->assertSame(
            50,
            (int) DB::table('materials')->where('uuid_id', 'eeeeeeee-0000-0000-0000-000000000001')->value('buy_price')
        );
        $this->assertNull(
            DB::table('materials')->where('uuid_id', 'eeeeeeee-0000-0000-0000-000000000002')->value('buy_price')
        );
    }

    /**
     * Задача 11.2: «белый список» колонок при вставке заказа выбрасывал `model_id`
     * (а вместе с ним `status`, `paid` и `user_order_number`), поэтому заказ, созданный
     * офлайн с ещё не синхронизированной моделью техники, приезжал на сервер (и на второе
     * устройство) **без модели**, а статус/оплата сбрасывались в дефолты БД.
     */
    public function test_order_insert_keeps_model_status_paid_and_user_order_number(): void
    {
        [$specializationId, $clientId] = $this->seedOrderDeps();

        $modelId = DB::table('equipment_models')->insertGetId([
            'name'              => 'Trek Marlin',
            'specialization_id' => $specializationId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $localId = 'abababab-1111-0000-0000-000000000011';

        $result = $this->sync([$this->insertOp('orders', $localId, [
            'specialization_id'    => $specializationId,
            'client_id'            => $clientId,
            'model_id'             => $modelId,
            'total_amount'         => 1234,
            'hours'                => 1,
            'minutes'              => 30,
            'status'               => 'done',
            'paid'                 => 1,
            'user_order_number'    => 42,
            'equipment_identifier' => 'VIN-123',
        ])]);

        $this->assertSame([], $result['errors']);
        $orderId = $result['synced'][0]['server_id'];

        $row = DB::table('orders')->where('id', $orderId)->first();
        $this->assertSame($modelId, (int) $row->model_id, 'Модель техники не должна теряться при вставке из синка');
        $this->assertSame('done', $row->status);
        $this->assertTrue((bool) $row->paid);
        $this->assertSame(42, (int) $row->user_order_number);
        $this->assertSame('VIN-123', $row->equipment_identifier);

        // Второе устройство получает заказ с той же моделью.
        $updates = $this->fetchUpdates('orders', 'test-device-2');
        $received = collect($updates['records'])->firstWhere('uuid_id', $localId);

        $this->assertNotNull($received);
        $this->assertSame($modelId, (int) $received['model_id']);
    }
}
