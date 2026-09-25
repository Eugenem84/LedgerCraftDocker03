<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Восстановление пароля (Фаза 16): письмо со ссылкой → приложение.
 *
 * Веб-версии приложения нет, поэтому проверяем, что письмо уходит с https-ссылкой
 * на bridge бэкенда (`/app/reset`), новый пароль ставится, а выданные токены
 * отзываются.
 *
 * Тест идёт на отдельной тестовой БД (см. `phpunit.xml`); на «не тестовой»
 * он пропускается (как `Phase10OnboardingTest`).
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || !str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты восстановления пароля запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();
    }

    public function test_forgot_password_sends_link_to_app_bridge(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->postJson('/api/forgot-password', ['email' => 'reset@example.com'])
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Письмо со ссылкой для сброса пароля отправлено. Проверьте почту — и папку «Спам».'
            );

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use ($user) {
                $url = $notification->toMail($user)->actionUrl;

                // Ссылка ведёт на https-bridge бэкенда (его открывает Android
                // App Links или bridge-страница со схемой приложения), а не на
                // страницу SPA — веб-версии у нас нет.
                return str_contains($url, (string) config('app-links.reset_path'))
                    && str_contains($url, 'token=')
                    && str_contains($url, urlencode('reset@example.com'));
            }
        );
    }

    public function test_forgot_password_for_unknown_email_returns_404(): void
    {
        $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com'])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Пользователь с таким email не зарегистрирован.');
    }

    public function test_forgot_password_validates_email(): void
    {
        $this->postJson('/api/forgot-password', ['email' => 'не-адрес'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_reset_password_updates_password_and_revokes_tokens(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset2@example.com',
            'password' => Hash::make('oldpass'),
        ]);
        $user->createToken('auth_token');

        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'reset2@example.com',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Пароль обновлён — войдите с новым паролем.');

        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));

        // Ранее выданные токены недействительны: на потерянном устройстве сессия обрывается.
        $this->assertSame(0, $user->tokens()->count());

        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset3@example.com']);

        $this->postJson('/api/reset-password', [
            'token' => 'invalid-token',
            'email' => 'reset3@example.com',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertStatus(422);
    }

    public function test_reset_password_validates_short_password(): void
    {
        $this->postJson('/api/reset-password', [
            'token' => 'whatever',
            'email' => 'reset@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
