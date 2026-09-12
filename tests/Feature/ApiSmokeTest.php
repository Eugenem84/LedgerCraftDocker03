<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Smoke-тест API (задача 5.6).
 *
 * Заменяет скаффолд Laravel (`GET /` → 200): корень `/` — это web-часть на Blade,
 * гости получают редирект на `/login`, и к API синка это отношения не имеет
 * (судьба web-версии — открытый вопрос, задача 7.6).
 *
 * Проверяем то, чем действительно пользуется приложение: API поднимается,
 * публичные маршруты проходят валидацию, а синк закрыт `auth:sanctum` (3.10).
 */
class ApiSmokeTest extends TestCase
{
    public function test_public_register_route_validates_input(): void
    {
        $this->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_sync_routes_are_closed_without_token(): void
    {
        $this->postJson('/api/sync', ['operations' => []])->assertUnauthorized();
        $this->getJson('/api/sync-updates?table=clients&since=0')->assertUnauthorized();
    }
}
