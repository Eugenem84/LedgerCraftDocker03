<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Подтверждение почты (Фаза 16) — **мягкое**: адрес подтверждаем, но синк и работу
 * не блокируем.
 *
 * Проверяем цепочку, на которую опирается приложение:
 *   • регистрация отправляет письмо со подписанной ссылкой на `verification.verify`;
 *   • переход по ссылке подтверждает адрес и уводит на https-адрес App Link
 *     (`/app/verified`), откуда открывается приложение;
 *   • испорченная/истёкшая ссылка не подтверждает адрес и отдаёт статус `invalid`;
 *   • повторная отправка требует входа и лимитирована.
 *
 * Тест идёт на отдельной тестовой БД (см. `phpunit.xml`).
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || !str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты подтверждения почты запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();
    }

    /** Подписанная ссылка та же, что уходит в письме (`VerifyEmailNotification`). */
    private function signedVerifyUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    public function test_register_sends_verification_email(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name' => 'Иван',
            'email' => 'verify@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertOk();

        $user = User::where('email', 'verify@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_verification_link_marks_email_verified_and_redirects_to_app_link(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->assertFalse($user->hasVerifiedEmail());

        $response = $this->get($this->signedVerifyUrl($user));

        $response->assertRedirect(
            url((string) config('app-links.verified_path')).'?status=verified'
        );

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // После подтверждения уходит приветственное письмо.
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_verification_link_without_signature_is_invalid_and_does_not_verify(): void
    {
        $user = User::factory()->unverified()->create();

        $url = '/email/verify/'.$user->getKey().'/'.sha1($user->getEmailForVerification());

        $this->get($url)->assertRedirect(
            url((string) config('app-links.verified_path')).'?status=invalid'
        );

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_link_with_wrong_hash_does_not_verify(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->getKey(),
            'hash' => sha1('другой@example.com'),
        ]);

        $this->get($url)->assertRedirect(
            url((string) config('app-links.verified_path')).'?status=invalid'
        );

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_resend_requires_auth_and_sends_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/email/verification-notification')->assertUnauthorized();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/email/verification-notification')
            ->assertStatus(202)
            ->assertJsonPath('message', 'Письмо отправлено повторно. Проверьте почту — и папку «Спам».');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_for_verified_email_returns_ok_and_sends_nothing(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/email/verification-notification')
            ->assertOk()
            ->assertJsonPath('message', 'Почта уже подтверждена.');

        Notification::assertNothingSent();
    }

    public function test_unverified_user_can_still_use_sync(): void
    {
        // Мягкая верификация: адрес не подтверждён, но синк работает — офлайн-первое
        // приложение не должно упираться в почту.
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/sync', ['operations' => []])
            ->assertOk();
    }
}
