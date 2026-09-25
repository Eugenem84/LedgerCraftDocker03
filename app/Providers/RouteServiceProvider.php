<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // Лимит на весь `/api/*` (включая preflight `OPTIONS`): ключ — пользователь
            // или IP (за одним роутером мастерской бывает несколько устройств).
            //
            // 60/мин оказалось мало: один проход синка — это 15 запросов выдачи плюс
            // столько же preflight'ов, то есть 30 обращений; два устройства за одним
            // IP уже выбирали лимит и получали «мигающие» 429 — синк «то идёт, то нет»
            // (дефект живого прогона 14.11). Preflight'ы кэшируются (`cors.max_age`),
            // а лимит поднят с запасом на несколько устройств и ручные действия.
            return Limit::perMinute(240)->by($request->user()?->id ?: $request->ip());
        });

        // --- Аутентификация и восстановление доступа (Фаза 10/16) ----------------
        // Отдельный жёсткий лимит на попытки входа: 5 в минуту на email+IP
        // (защита от перебора паролей).
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()));

        // Регистрация: 5 в минуту на IP — форма не должна становиться инструментом
        // массового создания аккаунтов.
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)
            ->by($request->ip()));

        // Повторная отправка письма подтверждения: 3 в минуту на пользователя+IP.
        RateLimiter::for('verification', fn (Request $request) => Limit::perMinute(3)
            ->by(($request->user()?->id ?: $request->ip()).'|'.$request->ip()));

        // Восстановление пароля: 5 в минуту на email+IP — форма не должна
        // становиться инструментом спама письмами по чужому адресу.
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(5)
            ->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()));

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
