<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Удаление аккаунта вместе с данными (Фаза 17).
 *
 * Ручка `DELETE /api/delete-account` существовала с 3.10, но фактически ничего не
 * удаляла у пользователя с данными: у части внешних ключей нет каскада
 * (`orders.user_id` → `users`, `clients`/`equipment_models` → `specializations`,
 * `products` → `product_categories`, `incoming_products` → `products`), а синкаемые
 * таблицы на soft-delete. Тест наполняет ДВА аккаунта по всем таблицам синка и
 * проверяет, что после удаления первого не остаётся ни его данных, ни токенов, ни
 * «хвостов» (`sync_tombstones`, `feedback_reports`), а данные второго целы.
 *
 * Тест выполняется на **отдельной** тестовой БД (см. `phpunit.xml`): `RefreshDatabase`
 * сносит таблицы, поэтому на «не тестовой» БД тест пропускается.
 */
class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Таблицы синка, у которых каждая из двух мастерских заводит ровно одну строку.
     * После удаления аккаунта-1 в каждой должно остаться ровно 1 (данные аккаунта-2).
     */
    private const OWNED_TABLES = [
        'specializations',
        'categories',
        'services',
        'clients',
        'equipment_models',
        'product_categories',
        'products',
        'product_stocks',
        'buy_product_prices',
        'incoming_products',
        'orders',
        'order_service',
        'order_product',
        'sales_products_prices',
        'materials',
        'sync_tombstones',
        'feedback_reports',
    ];

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || !str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты удаления аккаунта запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();
    }

    /**
     * Наполняет аккаунт по всем таблицам синка: профиль → каталог → склад → заказ с
     * позициями, плюс tombstone, отчёт и токен доступа.
     *
     * @return array<string, int>
     */
    private function seedAccount(User $user): array
    {
        $now = now();

        $specializationId = DB::table('specializations')->insertGetId([
            'specializationName' => 'Мастерская',
            'popularCounter' => 1,
            'user_id' => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Двигатель',
            'specialization_id' => $specializationId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $serviceId = DB::table('services')->insertGetId([
            'service' => 'Замена масла',
            'price' => 500,
            'category_id' => $categoryId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $clientId = DB::table('clients')->insertGetId([
            'name' => 'Иван',
            'phone' => '123',
            'specialization_id' => $specializationId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $modelId = DB::table('equipment_models')->insertGetId([
            'name' => 'Кросс',
            'specialization_id' => $specializationId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $productCategoryId = DB::table('product_categories')->insertGetId([
            'name' => 'Фильтры',
            'specialization_id' => $specializationId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $productId = DB::table('products')->insertGetId([
            'name' => 'Фильтр',
            'product_category_id' => $productCategoryId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('product_stocks')->insert([
            'product_id' => $productId, 'quantity' => 5, 'supplier' => '',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('buy_product_prices')->insert([
            'product_id' => $productId, 'buy_price' => 100,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('incoming_products')->insert([
            'product_id' => $productId, 'supplier' => '', 'quantity' => 5, 'by_price' => 100,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'specialization_id' => $specializationId,
            'client_id' => $clientId,
            'user_id' => $user->id,
            'model_id' => $modelId,
            'total_amount' => 1000,
            'status' => 'waiting',
            'paid' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('order_service')->insert([
            'order_id' => $orderId, 'service_id' => $serviceId, 'sale_price' => 500, 'quantity' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('order_product')->insert([
            'order_id' => $orderId, 'product_id' => $productId, 'sale_price' => 300, 'quantity' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('sales_products_prices')->insert([
            'order_id' => $orderId, 'product_id' => $productId, 'sale_price' => 300,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('materials')->insert([
            'order_id' => $orderId, 'name' => 'Герметик', 'price' => 50, 'amount' => 2,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Уникальность `(table_name, record_id)` в tombstone'ах — record_id берём из id
        // пользователя, чтобы два аккаунта не столкнулись на одном ключе.
        DB::table('sync_tombstones')->insert([
            'table_name' => 'materials', 'record_id' => $user->id, 'user_id' => $user->id,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('feedback_reports')->insert([
            'uuid_id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'kind' => 'bug',
            'message' => 'тест',
            'payload' => json_encode(['kind' => 'bug']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return compact('specializationId', 'clientId', 'orderId', 'productId');
    }

    private function makeUser(string $email): User
    {
        return User::factory()->create(['email' => $email]);
    }

    public function test_account_and_all_its_data_are_deleted_forever(): void
    {
        $doomed = $this->makeUser('doomed@example.com');
        $keeper = $this->makeUser('keeper@example.com');

        $this->seedAccount($doomed);
        $this->seedAccount($keeper);

        $token = $doomed->createToken('auth_token')->plainTextToken;

        $this->withToken($token)
            ->deleteJson('/api/delete-account')
            ->assertOk()
            ->assertJson(['message' => 'Аккаунт удалён']);

        // Пользователь и его токены исчезли; токен больше не открывает /api/me.
        $this->assertDatabaseMissing('users', ['id' => $doomed->id]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $doomed->id,
            'tokenable_type' => User::class,
        ]);

        // Sanctum кэширует пользователя, разрешённого в запросе удаления, поэтому
        // перед повторной проверкой токена сбрасываем guard'ы (тот же приём, что в
        // AuthSyncTest: сессионный guard иначе «побеждает» bearer-токен).
        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertStatus(401);

        // Во всех таблицах синка остались ровно строки второго аккаунта.
        foreach (self::OWNED_TABLES as $table) {
            $this->assertSame(
                1,
                DB::table($table)->count(),
                "в таблице {$table} остались строки удалённого аккаунта"
            );
        }

        $this->assertDatabaseCount('users', 1);

        // Данные второго аккаунта действительно его и доступны по-прежнему.
        $this->assertDatabaseHas('specializations', [
            'id' => DB::table('specializations')->value('id'),
            'user_id' => $keeper->id,
        ]);
        $keeperToken = $keeper->createToken('keeper_token')->plainTextToken;
        $this->withToken($keeperToken)->getJson('/api/me')->assertOk()->assertJson(['id' => $keeper->id]);
    }

    public function test_deleting_one_account_does_not_touch_another(): void
    {
        $doomed = $this->makeUser('doomed2@example.com');
        $keeper = $this->makeUser('keeper2@example.com');

        $this->seedAccount($doomed);
        $keeperIds = $this->seedAccount($keeper);

        $token = $doomed->createToken('auth_token')->plainTextToken;
        $this->withToken($token)->deleteJson('/api/delete-account')->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $keeperIds['orderId']]);
        $this->assertDatabaseHas('products', ['id' => $keeperIds['productId']]);
        $this->assertDatabaseHas('clients', ['id' => $keeperIds['clientId']]);
        $this->assertDatabaseHas('specializations', ['id' => $keeperIds['specializationId']]);
    }
}
