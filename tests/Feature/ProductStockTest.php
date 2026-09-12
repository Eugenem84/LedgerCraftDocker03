<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\ProductRepository;
use App\Repositories\ProductStockRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Склад: остаток и цены (задача 9.3).
 *
 * Что проверяем:
 *   • двойной источник «где лежит товар» убран: `product_stocks.product_categories_id`
 *     больше нет, категорию знает только `products.product_category_id`;
 *   • web-склад собирается из товаров (`LEFT JOIN` остатка): товар без строки остатка
 *     виден с нулём, чужая категория и soft-deleted товары не попадают;
 *   • `buy_product_prices` и `sales_products_prices` **читаются**: в выдаче товаров
 *     появились `buy_price` (последняя закупка) и `last_sale_price` (последняя продажа);
 *   • товар, приехавший синком, получает строку остатка (0) — «сколько лежит» живёт
 *     в одном месте;
 *   • цены продажи ходят синком и проверяют владельца (задача 3.10).
 *
 * Тест идёт на отдельной тестовой БД (см. `phpunit.xml`): `RefreshDatabase` сносит
 * таблицы, поэтому на «не тестовой» БД тест пропускается.
 */
class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || ! str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты склада запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Мастерская: специализация пользователя, категория товаров и товар в ней.
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
            'specializationId'  => $specializationId,
            'productCategoryId' => $productCategoryId,
            'productId'         => $productId,
        ];
    }

    private function addStock(int $productId, int $quantity): void
    {
        DB::table('product_stocks')->insert([
            'product_id' => $productId,
            'quantity'   => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_stock_row_does_not_duplicate_product_category(): void
    {
        $this->assertFalse(
            Schema::hasColumn('product_stocks', 'product_categories_id'),
            'Категория товара не должна дублироваться в остатке (задача 9.3)'
        );
        $this->assertTrue(Schema::hasColumn('product_stocks', 'product_id'));
    }

    public function test_stock_list_is_built_from_products(): void
    {
        $own = $this->seedProduct();
        $foreignCategory = $this->seedProduct();

        // В своей категории: товар с остатком, товар без строки остатка и удалённый товар.
        $this->addStock($own['productId'], 5);

        $withoutStock = DB::table('products')->insertGetId([
            'name'                => 'Без остатка',
            'product_category_id' => $own['productCategoryId'],
            'base_sale_price'     => 300,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $deleted = DB::table('products')->insertGetId([
            'name'                => 'Удалённый',
            'product_category_id' => $own['productCategoryId'],
            'created_at'          => now(),
            'updated_at'          => now(),
            'deleted_at'          => now(),
        ]);

        $rows = collect(app(ProductStockRepository::class)->getByProductCategory($own['productCategoryId']));

        $this->assertSame(['Без остатка', 'Фильтр'], $rows->pluck('name')->sort()->values()->all());
        $this->assertSame(0, (int) $rows->firstWhere('id', $withoutStock)->quantity);

        $withStock = $rows->firstWhere('id', $own['productId']);
        $this->assertSame(5, (int) $withStock->quantity);
        $this->assertSame(1000, (int) $withStock->base_sale_price);

        // Чужая категория и удалённый товар в выдачу не попадают.
        $this->assertNull($rows->firstWhere('id', $foreignCategory['productId']));
        $this->assertNull($rows->firstWhere('id', $deleted));
    }



    public function test_product_list_returns_stock_and_prices(): void
    {
        $product = $this->seedProduct();

        // Второй товар — в той же категории: проверяем, что «без истории цен» не ломает выдачу.
        $otherProductId = DB::table('products')->insertGetId([
            'name'                => 'Без истории',
            'product_category_id' => $product['productCategoryId'],
            'base_sale_price'     => 300,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $this->addStock($product['productId'], 7);
        $this->addStock($otherProductId, 1);

        // История закупок и продаж: в выдаче должны быть последние значения.
        DB::table('buy_product_prices')->insert([
            ['product_id' => $product['productId'], 'buy_price' => 400, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['product_id' => $product['productId'], 'buy_price' => 550, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $clientId = DB::table('clients')->insertGetId([
            'name'              => 'Клиент',
            'specialization_id' => $product['specializationId'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'specialization_id' => $product['specializationId'],
            'client_id'         => $clientId,
            'user_id'           => $this->user->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('sales_products_prices')->insert([
            ['product_id' => $product['productId'], 'order_id' => $orderId, 'sale_price' => 900, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['product_id' => $product['productId'], 'order_id' => $orderId, 'sale_price' => 1200, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $rows = collect(app(ProductRepository::class)->getByCategory($product['productCategoryId']));
        $row = $rows->firstWhere('id', $product['productId']);

        $this->assertSame(7, (int) $row->quantity);
        $this->assertSame(550, (int) $row->buy_price);
        $this->assertSame(1200, (int) $row->last_sale_price);

        // Без истории цен — пустые значения, а не ошибка.
        $plain = $rows->firstWhere('id', $otherProductId);
        $this->assertNull($plain->buy_price);
        $this->assertNull($plain->last_sale_price);
    }

    public function test_product_coming_from_sync_gets_a_stock_row(): void
    {
        $productCategoryId = $this->seedProduct()['productCategoryId'];

        $operation = [
            'id'      => 'op-product',
            'type'    => 'insert',
            'table'   => 'products',
            'payload' => [
                'local_id'            => '33333333-3333-3333-3333-333333333333',
                'name'                => 'Товар из приложения',
                'base_sale_price'     => 700,
                'product_category_id' => $productCategoryId,
            ],
        ];

        $response = $this->postJson('/api/sync', ['operations' => [$operation]], ['X-Sync-ID' => 'device-a']);
        $response->assertOk();
        $this->assertSame([], $response->json('errors'));

        $productId = (int) $response->json('synced.0.server_id');

        $this->assertSame(
            0,
            (int) DB::table('product_stocks')->where('product_id', $productId)->value('quantity'),
            'У товара из синка должна появиться строка остатка с нулём'
        );

        // Повторная отправка того же батча не создаёт вторую строку остатка.
        $this->postJson('/api/sync', ['operations' => [$operation]], ['X-Sync-ID' => 'device-a'])->assertOk();

        $this->assertSame(1, DB::table('product_stocks')->where('product_id', $productId)->count());
    }

    public function test_sales_price_comes_through_sync_and_is_owner_scoped(): void
    {
        $product = $this->seedProduct();

        $clientId = DB::table('clients')->insertGetId([
            'name'              => 'Клиент',
            'specialization_id' => $product['specializationId'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'specialization_id' => $product['specializationId'],
            'client_id'         => $clientId,
            'user_id'           => $this->user->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $localId = '44444444-4444-4444-4444-444444444444';

        $response = $this->postJson('/api/sync', ['operations' => [[
            'id'      => 'op-sale',
            'type'    => 'insert',
            'table'   => 'sales_products_prices',
            'payload' => [
                'local_id'   => $localId,
                'order_id'   => $orderId,
                'product_id' => $product['productId'],
                'sale_price' => 1500,
            ],
        ]]], ['X-Sync-ID' => 'device-a']);

        $response->assertOk();
        $this->assertSame([], $response->json('errors'));

        $this->assertSame(1, DB::table('sales_products_prices')->count());
        $this->assertSame($localId, DB::table('sales_products_prices')->value('uuid_id'));
        $this->assertSame(1500, (int) DB::table('sales_products_prices')->value('sale_price'));

        // Чужой заказ — привязывать к нему цену продажи нельзя (задача 3.10).
        // Заказ принадлежит другому пользователю напрямую (`user_id`), иначе «ничьи»
        // legacy-заказы считаются общими и проверка владельца их пропускает.
        $foreignUserId = User::factory()->create()->id;
        $foreignSpecializationId = $this->seedProduct($foreignUserId)['specializationId'];

        $foreignClientId = DB::table('clients')->insertGetId([
            'name'              => 'Чужой клиент',
            'specialization_id' => $foreignSpecializationId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $foreignOrderId = DB::table('orders')->insertGetId([
            'specialization_id' => $foreignSpecializationId,
            'client_id'         => $foreignClientId,
            'user_id'           => $foreignUserId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $foreign = $this->postJson('/api/sync', ['operations' => [[
            'id'      => 'op-sale-foreign',
            'type'    => 'insert',
            'table'   => 'sales_products_prices',
            'payload' => [
                'local_id'   => '55555555-5555-5555-5555-555555555555',
                'order_id'   => $foreignOrderId,
                'product_id' => $product['productId'],
                'sale_price' => 999,
            ],
        ]]], ['X-Sync-ID' => 'device-a']);

        $this->assertSame('FORBIDDEN_NOT_OWNER', $foreign->json('errors.0.error'));
        $this->assertSame(1, DB::table('sales_products_prices')->count());
    }
}
