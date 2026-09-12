<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Фаза 10 (задачи 10.5/10.6/10.7) — серверная часть мульти-профиля.
 *
 * Проверяем то, на что опирается клиент:
 *   • регистрация создаёт специализации вместе с пользователем (раньше — только `users`)
 *     и возвращает их для локального онбординга (без дублей в очереди синка);
 *   • занятый email → 422;
 *   • INSERT специализации через `/sync` доходит до колонки `specializationName`
 *     (клиент шлёт `name` — без сопоставления профиль не получал server_id);
 *   • endpoint пресетов закрыт авторизацией и отдаёт контент.
 *
 * Тест идёт на отдельной тестовой БД (см. `phpunit.xml`);
 * на «не тестовой» он пропускается (как `AuthSyncTest`).
 */
class Phase10OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'secret123';

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || !str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты Phase10 запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();
    }

    /** Токен Sanctum для свежего пользователя (как ходит настоящее приложение). */
    private function tokenFor(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }

    public function test_register_creates_specializations_and_returns_them(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'specializations' => [
                ['name' => 'Ремонт велосипедов', 'preset_key' => 'bike'],
                ['name' => 'Мастер по аквариумам', 'preset_key' => 'aquarium'],
            ],
        ])->assertOk()->assertJsonStructure(['access_token', 'user', 'specializations']);

        $this->assertCount(2, $response->json('specializations'));
        // Клиент читает `name`, `api.js` переименовывает `specializationName` → `name`.
        $this->assertSame('Ремонт велосипедов', $response->json('specializations.0.name'));
        $this->assertSame('bike', $response->json('specializations.0.preset_key'));

        $this->assertDatabaseHas('specializations', [
            'specializationName' => 'Ремонт велосипедов',
            'preset_key' => 'bike',
        ]);
        $this->assertDatabaseHas('specializations', [
            'specializationName' => 'Мастер по аквариумам',
            'preset_key' => 'aquarium',
        ]);
    }

    public function test_register_without_specializations_creates_a_default_profile(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Пётр',
            'email' => 'petr@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertOk()->assertJsonCount(1, 'specializations');

        $this->assertDatabaseHas('specializations', ['specializationName' => 'Пётр']);
    }

    public function test_register_with_busy_email_returns_422(): void
    {
        User::factory()->create(['email' => 'busy@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Ещё один',
            'email' => 'busy@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_specialization_insert_via_sync_maps_name_to_specialization_name(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        // `uuid_id` на сервере — тип uuid, клиент шлёт `crypto.randomUUID()`.
        $localId = '11111111-1111-4111-8111-111111111111';

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/sync', [
                'operations' => [[
                    'local_id' => $localId,
                    'type' => 'insert',
                    'table' => 'specializations',
                    'payload' => [
                        'local_id' => $localId,
                        'name' => 'Веломастерская',
                        'preset_key' => 'bike',
                        'accent' => '#4caf50',
                        'features' => '{"store":true}',
                        'archived' => 0,
                        'template_version' => 1,
                    ],
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('errors', []);

        $serverId = $response->json('synced.0.server_id');
        $this->assertNotEmpty($serverId, 'Специализации не был присвоен server_id');

        $this->assertDatabaseHas('specializations', [
            'id' => $serverId,
            'specializationName' => 'Веломастерская',
            'preset_key' => 'bike',
            'accent' => '#4caf50',
        ]);
    }

    public function test_templates_endpoint_requires_auth_and_returns_content(): void
    {
        DB::table('specialization_templates')->insert([
            'preset_key' => 'bike',
            'version' => 3,
            'content' => json_encode([
                'categories' => [['key' => 'wheels', 'name' => 'Колёса', 'services' => []]],
                'productCategories' => [['key' => 'spares', 'name' => 'Запчасти']],
                'models' => [['key' => 'mtb', 'name' => 'Горный']],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/specialization-templates')->assertUnauthorized();

        $user = User::factory()->create();
        $token = $this->tokenFor($user);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/specialization-templates')
            ->assertOk()
            ->assertJsonPath('templates.0.preset_key', 'bike')
            ->assertJsonPath('templates.0.version', 3)
            ->assertJsonPath('templates.0.content.categories.0.name', 'Колёса');
    }
}
