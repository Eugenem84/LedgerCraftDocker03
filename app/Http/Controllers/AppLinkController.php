<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Bridge-страницы для ссылок из писем: браузер → приложение.
 *
 * Веб-версии приложения пока нет, поэтому https-ссылки из писем не могут открыть
 * страницу сброса пароля. Здесь отдаётся лёгкая HTML-страница, которая сразу
 * пробует схему приложения (`ledgercraft://…`), а если приложение не установлено —
 * остаётся кнопка и ссылка на страницу загрузки.
 *
 * Если домен верифицирован для Android App Links, до этой страницы дело не
 * доходит: Android сам открывает приложение на https-ссылке.
 */
class AppLinkController extends Controller
{
    /**
     * Сброс пароля: переносим токен и email из https-ссылки в схему приложения.
     * Побочных эффектов нет — токен проверится при отправке нового пароля.
     */
    public function reset(Request $request)
    {
        return $this->bridge(
            'Сброс пароля в Ledger Craft',
            'Открываем приложение — там можно задать новый пароль.',
            $this->deepLink('reset_password', $request->only(['token', 'email']))
        );
    }

    /**
     * Результат подтверждения почты. Сюда уводит сервер после проверки подписи
     * (`EmailVerificationController@verify`).
     */
    public function verified(Request $request)
    {
        $verified = $request->query('status') === 'verified';

        return $this->bridge(
            $verified ? 'Почта подтверждена' : 'Ссылка недействительна',
            $verified
                ? 'Открываем приложение — почта подтверждена.'
                : 'Ссылка устарела или уже использована. Откройте приложение и отправьте письмо повторно.',
            $this->deepLink('verify_email', ['status' => $verified ? 'verified' : 'invalid'])
        );
    }

    private function bridge(string $title, string $message, string $deepLink)
    {
        return response()->view('app-link', [
            'title' => $title,
            'message' => $message,
            'deepLink' => $deepLink,
        ]);
    }

    /**
     * Собирает ссылку схемы приложения: `ledgercraft://<имя>?<query>`.
     *
     * @param  array<string, mixed>  $query
     */
    private function deepLink(string $name, array $query): string
    {
        $branch = (string) config("app-links.deep_links.{$name}");

        // Пустые параметры не тащим: у подтверждения почты нет token/email.
        $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');

        return (string) config('app-links.scheme').'://'.$branch.'?'.http_build_query($query);
    }
}
