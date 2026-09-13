<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Доступ к выгрузке отчётов «Сообщить об ошибке» (Фаза 14, задача 14.6).
 *
 * Отчёты читает разработчик/ИИ-агент, а не мастер, поэтому здесь **отдельный**
 * pull-токен (`X-Feedback-Token`), а не пользовательский bearer: обычный токен
 * мастерской чужие отчёты не отдаёт. Токен сравниваем в постоянном времени
 * (`hash_equals`), пустой конфиг — это «выгрузка выключена» (503), а не «пускаем всех».
 */
class EnsureFeedbackPullToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('feedback.pull_token');

        if ($expected === '') {
            return response()->json(
                ['error' => 'FEEDBACK_PULL_TOKEN_NOT_CONFIGURED'],
                503
            );
        }

        $provided = (string) $request->header('X-Feedback-Token', '');

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['error' => 'FORBIDDEN_FEEDBACK_TOKEN'], 403);
        }

        return $next($request);
    }
}
