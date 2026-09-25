<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Подтверждение почты по ссылке из письма (мягкая верификация).
 *
 * Ссылка подписанная и с истечением; подпись проверяем вручную, а не
 * `signed`-middleware, чтобы истёкшая ссылка давала понятный статус, а не 403.
 * После проверки уводим на https-адрес App Link (`/app/verified`): если приложение
 * установлено и домен верифицирован, Android откроет его сразу, иначе браузер
 * покажет bridge-страницу со ссылкой на схему приложения.
 *
 * Подтверждение **не блокирует** работу/синк — приложение лишь показывает баннер
 * и предлагает отправить письмо повторно.
 */
class EmailVerificationController extends Controller
{
    /**
     * Подтверждение почты по ссылке из письма.
     */
    public function verify(Request $request, string $id, string $hash)
    {
        $status = 'invalid';

        if ($request->hasValidSignature()) {
            $user = User::find($id);

            // Хэш страхует от подмены id в ссылке
            if ($user && hash_equals($hash, sha1($user->getEmailForVerification()))) {
                if (! $user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                    event(new Verified($user));
                    $this->sendWelcome($user);
                }

                $status = 'verified';
            }
        }

        return redirect()->to(
            url((string) config('app-links.verified_path')).'?status='.$status
        );
    }

    /**
     * Повторная отправка письма с ссылкой подтверждения (из приложения).
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Почта уже подтверждена.'], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(
            ['message' => 'Письмо отправлено повторно. Проверьте почту — и папку «Спам».'],
            202
        );
    }

    /**
     * Приветствие после подтверждения. Сбой письма не должен ломать переход
     * по ссылке.
     */
    private function sendWelcome(User $user): void
    {
        try {
            $user->notify(new WelcomeNotification);
        } catch (Throwable $e) {
            Log::warning('Welcome email failed: '.$e->getMessage(), ['user_id' => $user->id]);
        }
    }
}
