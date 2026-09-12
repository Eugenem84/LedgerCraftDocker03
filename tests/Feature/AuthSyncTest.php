<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Вход и токен Sanctum — BE-часть задачи 7.4.
 *
 * Код `/api/login` (и `/api/logout`, `/api/me`) существует с 3.10, но критерий
 * «вход защищён, данные разделены по пользователям» до сих пор не был подтверждён
 * тестом. Здесь проверяем сквозной путь, на который опирается клиент:
 *   • вход по email/паролю выдаёт `access_token`;
 *   • этим токеном действительно открывается `/api/sync` (а не только `/me`);
 *   • без токена синк недоступен (401);
 *   • выход (`/api/logout`) отзывает токен — повторный запрос с ним уже 401;
 *   • токен определяет владельца данных (`/api/me` отдаёт его владельца).
 *
 * Изоляция записей по владельцу (IDOR) подробно проверена в `SyncControllerTest`
 * (3.10); здесь — только связка «вход → токен → синк».
 *
 * Тест выполняется на **отдельной** тестовой БД (см. `phpunit.xml`):
 * `RefreshDatabase` сносит таблицы, поэтому на «не тестовой» БД тест пропускается.
 */
class AuthSyncTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'master@example.com';
    private const PASSWORD = 'secret123';

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || !str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты AuthSync запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();
    }

    private function makeUser(): User
    {
        return User::factory()->create([
            'email'    => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
        ]);
    }

    /** Вход и получение токена доступа. */
    private function login(): string
    {
        $token = $this->postJson('/api/login', [
            'email'    => self::EMAIL,
            'password' => self::PASSWORD,
        ])->assertOk()->json('access_token');

        $this->assertNotEmpty($token, 'Сервер не вернул access_token');

        // Вход использует сессионный guard, а тестовый клиент ходит без сессии (как
        // настоящее приложение). Sanctum проверяет сессию РАНЬШЕ bearer-токена: пока
        // она жива, `currentAccessToken()` вернёт `TransientToken` и `/logout` упадёт.
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $token;
    }

    public function test_login_returns_token_that_opens_sync(): void
    {
        $this->makeUser();

        $this->postJson('/api/login', [
            'email'    => self::EMAIL,
            'password' => self::PASSWORD,
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'user'])
            ->assertJsonPath('token_type', 'Bearer');

        $token = $this->login();

        // Токен из входа открывает синк, а не только /me.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/sync', ['operations' => []])
            ->assertOk()
            ->assertJsonStructure(['synced', 'errors']);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $this->makeUser();

        $this->postJson('/api/login', [
            'email'    => self::EMAIL,
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_sync_is_unavailable_without_token(): void
    {
        $this->postJson('/api/sync', ['operations' => []])->assertUnauthorized();
    }

    public function test_logout_invalidates_token(): void
    {
        $this->makeUser();
        $token = $this->login();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertOk();

        // Токен должен быть удалён из БД, а не просто «забыт» клиентом.
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // После выхода тот же токен больше не работает.
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/sync', ['operations' => []])
            ->assertUnauthorized();
    }

    public function test_token_identifies_the_data_owner(): void
    {
        $user = $this->makeUser();
        $token = $this->login();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }
}
