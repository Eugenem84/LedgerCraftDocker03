<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

/**
 * Восстановление пароля (ссылка из письма → приложение).
 *
 * Письмо формирует `ResetPasswordNotification`: ссылка ведёт на https-bridge
 * бэкенда (`/app/reset`), который уводит пользователя в приложение. Здесь только
 * приём запросов из приложения.
 *
 * Минимальная длина пароля совпадает с регистрацией (`min:6`) — правила не должны
 * расходиться между экранами.
 */
class PasswordResetController extends Controller
{
    /**
     * Запрос письма со ссылкой для сброса пароля.
     *
     * Адрес проверяем явно: если такой почты нет — отдаём `404`, чтобы
     * пользователь сразу видел опечатку, а не ждал письмо, которого не будет.
     * Это осознанный компромисс (по ответу можно узнать, зарегистрирован адрес
     * или нет): для UX это важнее скрытности.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            // Технические детали — только в лог, клиенту нейтральное сообщение
            Log::error('Password reset link error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'Не удалось отправить письмо. Попробуйте ещё раз позже.',
            ], 500);
        }

        if ($status === Password::INVALID_USER) {
            return response()->json([
                'message' => 'Пользователь с таким email не зарегистрирован.',
            ], 404);
        }

        // Повторное письмо раньше чем через 60 секунд Laravel не отправляет.
        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'message' => 'Письмо уже отправлено недавно. Проверьте почту — и папку «Спам».',
            ]);
        }

        return response()->json([
            'message' => 'Письмо со ссылкой для сброса пароля отправлено. Проверьте почту — и папку «Спам».',
        ]);
    }

    /**
     * Установка нового пароля по токену из письма.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();

                    // После смены пароля ранее выданные токены недействительны:
                    // на потерянном/чужом устройстве сессия обрывается.
                    $user->tokens()->delete();

                    event(new PasswordReset($user));

                    // Предупреждаем владельца: смену пароля могли инициировать не он.
                    // Сбой письма не должен ломать сброс.
                    try {
                        $user->notify(new PasswordChangedNotification);
                    } catch (Throwable $e) {
                        Log::warning('Password changed email failed: '.$e->getMessage(), ['user_id' => $user->id]);
                    }
                }
            );
        } catch (Throwable $e) {
            Log::error('Password reset error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'Не удалось сменить пароль. Попробуйте ещё раз.',
            ], 500);
        }

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Ссылка недействительна или устарела. Запросите письмо заново.',
            ], 422);
        }

        return response()->json([
            'message' => 'Пароль обновлён — войдите с новым паролем.',
        ]);
    }
}
