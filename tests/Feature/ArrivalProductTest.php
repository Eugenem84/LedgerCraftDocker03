<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Приход товара (задача 9.2).
 *
 * Было: `POST /api/arrival_product` не возвращал тело, делал три записи без транзакции
 * и без идемпотентности — повторный приход удваивал остаток, а у товара, заведённого
 * приложением (уехал в синк), строки остатка не было вовсе, поэтому приход падал.
 *
 * Стало: приход (и web-ручка, и синк) проходит через
 * `IncomingProductRepository::recordArrival()`, где идемпотентность — по клиентскому
 * `uuid_id`, а склад увеличивается **ровно один раз** на приход; строка остатка
 * создаётся по требованию. Владелец остатка определяется товаром.
 *
 * Тест идёт на отдельной тестовой БД (см. `phpunit.xml`): `RefreshDatabase` сносит
 * таблицы, поэтому на «не тестовой» БД тест пропускается.
 */
class ArrivalProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || ! str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты прихода запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Товар мастерской: специализация пользователя → категория → товар.
     *
     * @return array{specializationId: int, productCategoryId: int, productId: int}
     */
    private function seedProduct(?int $userId = null): array
    {
        $userId ??= $this->user->id;

        $specializationId = DB::table('specializations')->insertGetId([
            'specializationName' => 'Склад',
            'popularCounter'     => 0,
            'user_id'            => $userId,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $productCategoryId = DB::table('product_categories')->insertGetId([
            'name'              => 'Фильтры',
            'specialization_id' => $specializationId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'name'                => 'Фильтр',
            'product_category_id' => $productCategoryId,
            'base_sale_price'     => 1000,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return [
            'specializationId'   => $specializationId,
            'productCategoryId'  => $productCategoryId,
            'productId'          => $productId,
        ];
    }

    /** @return array{stock:int|null, arrivals:int, salePrice:int|null} */
    private function warehouseState(int $productId): array
    {
        return [
            'stock'     => DB::table('product_stocks')->where('product_id', $productId)->value('quantity'),
            'arrivals'  => DB::table('incoming_products')->where('product_id', $productId)->count(),
            'salePrice' => DB::table('products')->where('id', $productId)->value('base_sale_price'),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $operations
     * @return array{synced: array, errors: array}
     */
    private function sync(array $operations): array
    {
        $response = $this->postJson('/api/sync', ['operations' => $operations], ['X-Sync-ID' => 'device-a']);
        $response->assertOk();

        return [
            'synced' => $response->json('synced') ?? [],
            'errors' => $response->json('errors') ?? [],
        ];
    }

    public function test_web_arrival_creates_stock_incoming_record_and_response(): void
    {
        $productId = $this->seedProduct()['productId'];

        $response = $this->postJson('/api/arrival_product', [
            'product_id'       => $productId,
            'arrival_quantity' => 10,
            'by_price'         => '1 500,50', // деньги — целыми рублями (3.12)
            'base_sale_price'  => 2000,
            'uuid_id'          => '11111111-1111-1111-1111-111111111111',
        ]);

        $response->assertCreated()
            ->assertJsonPath('idempotent', false)
            ->assertJsonPath('quantity', 10)
            ->assertJsonPath('stock_quantity', 10);
        $this->assertNotEmpty($response->json('message'));

        // Строка остатка создаётся по требованию (товар приложением мог её не иметь).
        $this->assertSame(10, $this->warehouseState($productId)['stock']);
        $this->assertSame(1, $this->warehouseState($productId)['arrivals']);
        $this->assertSame(2000, $this->warehouseState($productId)['salePrice']);
        $this->assertSame(
            1501,
            (int) DB::table('incoming_products')
                ->where('uuid_id', '11111111-1111-1111-1111-111111111111')
                ->value('by_price')
        );
    }

    public function test_repeated_web_arrival_does_not_double_the_stock(): void
    {
        $productId = $this->seedProduct()['productId'];
        $payload = [
            'product_id'       => $productId,
            'arrival_quantity' => 10,
            'by_price'         => 300,
            'uuid_id'          => '22222222-2222-2222-2222-222222222222',
        ];

        $first = $this->postJson('/api/arrival_product', $payload);
        $first->assertCreated()->assertJsonPath('idempotent', false);

        // Повтор (двойной клик/ретрай) — тот же `uuid_id`: склад не растёт.
        $second = $this->postJson('/api/arrival_product', $payload);
        $second->assertOk()->assertJsonPath('idempotent', true)->assertJsonPath('stock_quantity', 10);

        $this->assertSame(10, $this->warehouseState($productId)['stock']);
        $this->assertSame(1, $this->warehouseState($productId)['arrivals']);
    }

    /**
     * `uuid_id` на сервере — тип `uuid`: мусорный id не должен валить приход
     * (`SQLSTATE 22P02`), иначе операция синка зациклилась бы в очереди.
     */
    public function test_non_uuid_identifier_does_not_break_the_arrival(): void
    {
        $productId = $this->seedProduct()['productId'];

        $this->postJson('/api/arrival_product', [
            'product_id'       => $productId,
            'arrival_quantity' => 5,
            'by_price'         => 100,
            'uuid_id'          => 'arrival-x',
        ])->assertCreated()->assertJsonPath('stock_quantity', 5);

        $this->assertSame(5, $this->warehouseState($productId)['stock']);
        $this->assertSame(1, $this->warehouseState($productId)['arrivals']);
        $this->assertNull(DB::table('incoming_products')->where('product_id', $productId)->value('uuid_id'));
    }

    public function test_web_arrival_without_uuid_is_a_new_receipt(): void
    {
        $productId = $this->seedProduct()['productId'];

        $this->postJson('/api/arrival_product', [
            'product_id'       => $productId,
            'arrival_quantity' => 4,
            'by_price'         => 100,
        ])->assertCreated();

        $this->postJson('/api/arrival_product', [
            'product_id'       => $productId,
            'arrival_quantity' => 6,
            'by_price'         => 120,
        ])->assertCreated();

        // Два разных прихода (без ключа идемпотентности) — два документа и остаток 10.
        $this->assertSame(10, $this->warehouseState($productId)['stock']);
        $this->assertSame(2, $this->warehouseState($productId)['arrivals']);
    }

    /**
     * Задача 11.7 (бывший O-6): ручка больше не открыта всем.
     * Без токена и без сессии — 401, до записи дело не доходит.
     */
    public function test_web_arrival_requires_auth(): void
    {
        $productId = $this->seedProduct()['productId'];

        // «Забываем» пользователя, которого поставил setUp (`Sanctum::actingAs`), —
        // запрос должен быть анонимным, как у стороннего клиента без токена.
        $this->app['auth']->forgetGuards();

        $this->postJson('/api/arrival_product', [
            'product_id'       => $productId,
            'arrival_quantity' => 3,
            'by_price'         => 100,
        ])->assertUnauthorized();

        $this->assertNull($this->warehouseState($productId)['stock']);
        $this->assertSame(0, $this->warehouseState($productId)['arrivals']);
    }

    /**
     * Задача 11.7: чужой товар через web-ручку не приходуется. Владелец проверяется
     * той же цепочкой, что в синке (3.10), и ошибка названа так же (`FORBIDDEN_NOT_OWNER`).
     */
    public function test_web_arrival_for_foreign_product_is_rejected(): void
    {
        $foreignProductId = $this->seedProduct(User::factory()->create()->id)['productId'];

        $this->postJson('/api/arrival_product', [
            'product_id'       => $foreignProductId,
            'arrival_quantity' => 5,
            'by_price'         => 100,
        ])->assertForbidden()->assertJsonPath('error', 'FORBIDDEN_NOT_OWNER');

        $this->assertNull($this->warehouseState($foreignProductId)['stock']);
        $this->assertSame(0, $this->warehouseState($foreignProductId)['arrivals']);
    }

    /**
     * Задача 11.7: web-часть входит по сессии (без bearer-токена) — приход работает.
     * Запрос идёт с «frontend»-Referer (как из браузера), чтобы отработал
     * first-party путь Sanctum (`EnsureFrontendRequestsAreStateful`).
     */
    public function test_web_arrival_works_with_session_user_without_token(): void
    {
        $productId = $this->seedProduct()['productId'];

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->user, 'web');

        $this->postJson('/api/arrival_product', [
            'product_id'       => $productId,
            'arrival_quantity' => 6,
            'by_price'         => 100,
        ], ['Referer' => 'http://localhost'])->assertCreated()->assertJsonPath('stock_quantity', 6);

        $this->assertSame(6, $this->warehouseState($productId)['stock']);
        $this->assertSame(1, $this->warehouseState($productId)['arrivals']);
    }

    /** Операция синка: приход товара с клиентским id (ключ идемпотентности). */
    private function arrivalOp(int $productId, string $localId, int $quantity, int $byPrice = 300): array
    {
        return [
            'id'      => 'op-'.$localId,
            'type'    => 'insert',
            'table'   => 'incoming_products',
            'payload' => [
                'local_id'   => $localId,
                'product_id' => $productId,
                'quantity'   => $quantity,
                'by_price'   => $byPrice,
                'supplier'   => '',
            ],
        ];
    }

    public function test_sync_arrival_increments_stock_once(): void
    {
        $productId = $this->seedProduct()['productId'];
        $operation = $this->arrivalOp($productId, '99999999-0000-0000-0000-000000000001', 7);

        $first = $this->sync([$operation]);
        $this->assertSame([], $first['errors']);
        $this->assertNotNull($first['synced'][0]['server_id']);
        $this->assertSame(7, $first['synced'][0]['stock_quantity']);

        $this->assertSame(7, $this->warehouseState($productId)['stock']);
        $this->assertSame(1, $this->warehouseState($productId)['arrivals']);

        // Повторная отправка того же батча (ответ потерялся) — склад тот же.
        $second = $this->sync([$operation]);
        $this->assertSame([], $second['errors']);
        $this->assertSame($first['synced'][0]['server_id'], $second['synced'][0]['server_id']);

        $this->assertSame(7, $this->warehouseState($productId)['stock']);
        $this->assertSame(1, $this->warehouseState($productId)['arrivals']);
    }

    public function test_sync_arrival_rejects_broken_operations(): void
    {
        $productId = $this->seedProduct()['productId'];

        $invalidQuantity = $this->arrivalOp($productId, '99999999-0000-0000-0000-000000000002', 0);
        $missingProduct = $this->arrivalOp($productId, '99999999-0000-0000-0000-000000000003', 3);
        unset($missingProduct['payload']['product_id']);

        $result = $this->sync([$invalidQuantity, $missingProduct]);

        $this->assertCount(2, $result['errors']);
        $this->assertSame('INVALID_QUANTITY', $result['errors'][0]['error']);
        $this->assertSame('MISSING_PRODUCT_ID', $result['errors'][1]['error']);

        $this->assertNull($this->warehouseState($productId)['stock']);
        $this->assertSame(0, $this->warehouseState($productId)['arrivals']);
    }

    public function test_arrival_for_foreign_product_is_rejected(): void
    {
        $foreignProductId = $this->seedProduct(User::factory()->create()->id)['productId'];

        $result = $this->sync([
            $this->arrivalOp($foreignProductId, '99999999-0000-0000-0000-000000000004', 5),
        ]);

        $this->assertSame('FORBIDDEN_NOT_OWNER', $result['errors'][0]['error']);
        $this->assertNull($this->warehouseState($foreignProductId)['stock']);
        $this->assertSame(0, $this->warehouseState($foreignProductId)['arrivals']);
    }

    public function test_device_sees_only_its_own_stock_rows(): void
    {
        $ownProductId = $this->seedProduct()['productId'];
        $foreignProductId = $this->seedProduct(User::factory()->create()->id)['productId'];

        DB::table('product_stocks')->insert([
            ['product_id' => $ownProductId, 'quantity' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $foreignProductId, 'quantity' => 99, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->getJson('/api/sync-updates?table=product_stocks&since=0', ['X-Sync-ID' => 'device-a']);
        $response->assertOk();

        $records = collect($response->json('records'));
        $this->assertCount(1, $records, 'Устройство не должно видеть остатки чужого пользователя');
        $this->assertSame((int) $ownProductId, (int) $records[0]['product_id']);
    }
}
