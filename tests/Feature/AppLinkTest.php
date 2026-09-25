<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Bridge-страницы ссылок из писем (Фаза 16).
 *
 * Клиент — Android-приложение, веб-версии пока нет, поэтому письмо ведёт на
 * https-адрес бэкенда, а страница уводит пользователя в приложение по схеме
 * `ledgercraft://…` (Android App Links сработали бы раньше и страницу не показали).
 *
 * БД не нужна — проверяем только ответы роутов.
 */
class AppLinkTest extends TestCase
{
    public function test_reset_bridge_page_links_into_app(): void
    {
        $response = $this->get('/app/reset?token=tok123&email=ivan%40example.com');

        $response->assertOk()
            ->assertSee('Сброс пароля в Ledger Craft')
            // Схема приложения и параметры — в href кнопки; `&` в HTML экранируется,
            // поэтому проверяем фрагменты по отдельности.
            ->assertSee('ledgercraft://reset-password', false)
            ->assertSee('token=tok123', false)
            ->assertSee('ivan%40example.com', false);
    }

    public function test_reset_bridge_page_without_params_still_opens_app(): void
    {
        $this->get('/app/reset')
            ->assertOk()
            ->assertSee('ledgercraft://reset-password', false);
    }

    public function test_verified_bridge_page_reports_success(): void
    {
        $this->get('/app/verified?status=verified')
            ->assertOk()
            ->assertSee('Почта подтверждена')
            ->assertSee('ledgercraft://verify-email?status=verified', false);
    }

    public function test_verified_bridge_page_reports_invalid_link(): void
    {
        $this->get('/app/verified?status=invalid')
            ->assertOk()
            ->assertSee('Ссылка недействительна')
            ->assertSee('ledgercraft://verify-email?status=invalid', false);
    }

    public function test_bridge_paths_match_config(): void
    {
        // Пути не хардкожены: письма и route-файл берут их из `config/app-links.php`.
        $this->assertSame('/app/reset', config('app-links.reset_path'));
        $this->assertSame('/app/verified', config('app-links.verified_path'));
        $this->assertSame('ledgercraft', config('app-links.scheme'));
        $this->assertSame('reset-password', config('app-links.deep_links.reset_password'));
        $this->assertSame('verify-email', config('app-links.deep_links.verify_email'));
    }
}
