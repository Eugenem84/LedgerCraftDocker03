<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Публичная ссылка на отчёт — задача 9.4.
 *
 * Критерий: «ссылка реально генерируется и открывается». Проверяем обе половины:
 *   • генерация — `POST /api/order-report/{order}/share-link` под `auth:sanctum`,
 *     только владелец заказа, повторный запрос отдаёт ту же ссылку (иначе уже
 *     отправленные клиенту ссылки ломались бы);
 *   • открытие — `GET /order-report/{order}?token=...` рендерит Blade-страницу
 *     отчёта, а без токена/с чужим токеном отдаёт 404 (раньше `token` игнорировался
 *     и отчёт читался по одному id заказа — перебор id давал чужие отчёты).
 *
 * Отдельно фиксируем связку с синком: правка заказа с клиента не должна затирать
 * `share_token` (клиент его не знает и присылал `null` — выданная ссылка умирала).
 *
 * Тест выполняется на **отдельной** тестовой БД (см. `phpunit.xml`):
 * `RefreshDatabase` сносит таблицы, поэтому на «не тестовой» БД тест пропускается.
 */
class OrderShareLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || !str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты OrderShareLink запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Заказ с клиентом и ручной позицией — чтобы страница отчёта реально рендерилась.
     *
     * @param int|null $userId владелец заказа; `null` — «ничий» (данные до 3.10)
     */
    private function seedOrder(?int $userId = null): int
    {
        $specializationId = DB::table('specializations')->insertGetId([
            'specializationName' => 'Тестовая специализация',
            'popularCounter'     => 0,
            'user_id'            => $this->user->id,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $clientId = DB::table('clients')->insertGetId([
            'name'              => 'Клиент-тест',
            'phone'             => '555-55-55',
            'specialization_id' => $specializationId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'specialization_id' => $specializationId,
            'client_id'         => $clientId,
            'total_amount'      => 1500,
            'user_id'           => $userId ?? $this->user->id,
            'status'            => 'waiting',
            'paid'              => false,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('materials')->insert([
            'order_id'   => $orderId,
            'name'       => 'Герметик',
            'price'      => 500,
            'amount'     => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $orderId;
    }

    /** Генерирует ссылку от имени пользователя и возвращает её URL. */
    private function shareLink(int $orderId): string
    {
        Sanctum::actingAs($this->user);

        return $this->postJson("/api/order-report/{$orderId}/share-link")
            ->assertOk()
            ->json('url');
    }

    /** Из абсолютного URL делает путь с query — его принимает тестовый HTTP-клиент. */
    private function pathWithQuery(string $url): string
    {
        $parts = parse_url($url);

        return ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    public function test_guest_cannot_generate_share_link(): void
    {
        $orderId = $this->seedOrder();

        $this->postJson("/api/order-report/{$orderId}/share-link")->assertUnauthorized();

        // Токен не должен появиться «по пути» — иначе гость всё равно открыл бы отчёт.
        $this->assertNull(DB::table('orders')->where('id', $orderId)->value('share_token'));
    }

    public function test_owner_gets_public_url_with_token(): void
    {
        $orderId = $this->seedOrder();

        $url = $this->shareLink($orderId);
        $token = DB::table('orders')->where('id', $orderId)->value('share_token');

        $this->assertNotEmpty($token, 'Сервер не сохранил share_token');
        $this->assertStringContainsString("/order-report/{$orderId}", $url);
        $this->assertStringContainsString('token='.$token, $url);
    }

    public function test_repeated_request_returns_the_same_link(): void
    {
        $orderId = $this->seedOrder();

        // Ссылка, однажды отправленная клиенту, не должна ломаться повторным запросом.
        $this->assertSame($this->shareLink($orderId), $this->shareLink($orderId));
    }

    public function test_foreign_order_is_not_found(): void
    {
        $orderId = $this->seedOrder();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/order-report/{$orderId}/share-link")->assertNotFound();

        $this->assertNull(
            DB::table('orders')->where('id', $orderId)->value('share_token'),
            'Чужой запрос не должен создавать токен'
        );
    }

    public function test_public_url_renders_report_with_valid_token(): void
    {
        $orderId = $this->seedOrder();
        $url = $this->shareLink($orderId);

        // Самая важная проверка критерия: ссылка действительно открывается и показывает отчёт.
        $this->get($this->pathWithQuery($url))
            ->assertOk()
            ->assertSee('Клиент-тест')
            ->assertSee('Герметик')
            ->assertSee('1500');
    }

    public function test_missing_or_wrong_token_is_rejected(): void
    {
        $orderId = $this->seedOrder();
        $this->shareLink($orderId);

        $this->get("/order-report/{$orderId}")->assertNotFound();
        $this->get("/order-report/{$orderId}?token=не-тот-токен")->assertNotFound();
    }

    public function test_order_without_share_token_has_no_public_report(): void
    {
        $orderId = $this->seedOrder();

        // Ссылку не выдавали: даже «угадав» любой токен, отчёт не открыть.
        $this->get("/order-report/{$orderId}?token=whatever")->assertNotFound();
    }

    public function test_soft_deleted_order_has_no_report(): void
    {
        $orderId = $this->seedOrder();
        $url = $this->shareLink($orderId);

        DB::table('orders')->where('id', $orderId)->update(['deleted_at' => now()]);

        $this->get($this->pathWithQuery($url))->assertNotFound();

        Sanctum::actingAs($this->user);
        $this->postJson("/api/order-report/{$orderId}/share-link")->assertNotFound();
    }

    public function test_sync_update_does_not_wipe_share_token(): void
    {
        $orderId = $this->seedOrder();
        $this->shareLink($orderId);
        $token = DB::table('orders')->where('id', $orderId)->value('share_token');

        // Клиент правит заказ и присылает свой `share_token` (у него он пустой —
        // токен создаёт только сервер). Раньше это затирало выданную ссылку.
        Sanctum::actingAs($this->user);
        $this->postJson('/api/sync', ['operations' => [[
            'id'      => 'op-update',
            'type'    => 'update',
            'table'   => 'orders',
            'payload' => [
                'id'           => $orderId,
                'share_token'  => null,
                'total_amount' => 2000,
            ],
        ]]])->assertOk()->assertJsonPath('errors', []);

        $this->assertSame(2000, (int) DB::table('orders')->where('id', $orderId)->value('total_amount'));
        $this->assertSame($token, DB::table('orders')->where('id', $orderId)->value('share_token'));
    }
}
