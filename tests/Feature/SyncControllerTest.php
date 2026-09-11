<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Идемпотентность и изоляция операций синка (задача 3.5).
 *
 * Критерий задачи: «повторная отправка того же батча не создаёт дублей».
 * Дополнительно проверяем контракт, на который опирается клиент:
 *   • каждая операция получает **явный** ответ (`synced`) — клиент считает
 *     операцию доставленной только по нему (FE-половина 3.5);
 *   • битая операция не срывает остальной батч (SAVEPOINT-изоляция из Go, 3.11);
 *   • `order_service` (без PK) дедуплицируется по натуральному ключу
 *     `order_id + service_id` и удаляется по нему же;
 *   • `server_id`/`*_server_id` из payload не попадают в реальные колонки.
 *
 * Тест выполняется на **отдельной** тестовой БД (см. `phpunit.xml`):
 * `RefreshDatabase` сносит таблицы, поэтому на «не тестовой» БД тест
 * пропускается (setUp).
 */
class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertSame(0, DB::table('order_service')->count());
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
        $this->assertSame(0, DB::table('orders')->where('id', $orderId)->count());
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
}
