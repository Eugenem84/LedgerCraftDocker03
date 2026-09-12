<?php

namespace Tests\Feature;

use App\Models\SpecializationTemplate;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SpecializationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Фаза 11 (задача 11.3) — сид пресетов специализаций.
 *
 * Критерий «Готово»: `GET /api/specialization-templates` отдаёт 4 пресета
 * (`bike`/`aquarium`/`hvac`/`auto`), а правка контента на сервере меняет каталог
 * нового пользователя без релиза приложения (критерий 10.7). Проверяем:
 *   • сид создаёт четыре строки с непустым каталогом (категории → услуги с ценами,
 *     категории товаров, модели);
 *   • повторный запуск идемпотентен и **освежает** контент (правка без пересборки клиента);
 *   • `DatabaseSeeder` действительно тянет пресеты (иначе `db:seed` ничего не даст);
 *   • endpoint отдаёт все четыре пресета под `auth:sanctum`.
 *
 * Тест идёт на отдельной тестовой БД (см. `phpunit.xml`);
 * на «не тестовой» он пропускается (как `Phase10OnboardingTest`).
 */
class SpecializationTemplateSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ниши v1 (решение D5) — должны совпадать с `src/domain/presets/*.js` на клиенте.
     * Порядок алфавитный: так же сортирует выдача endpoint (`orderBy('preset_key')`).
     */
    private const PRESET_KEYS = ['aquarium', 'auto', 'bike', 'hvac'];

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || !str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты сида пресетов запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();
    }

    public function test_seeder_creates_four_presets_with_catalog_content(): void
    {
        $this->seed(SpecializationTemplateSeeder::class);

        $templates = SpecializationTemplate::query()->orderBy('preset_key')->get();

        $this->assertCount(4, $templates);
        $this->assertSame(self::PRESET_KEYS, $templates->pluck('preset_key')->all());

        foreach ($templates as $template) {
            $content = $template->content;

            $this->assertGreaterThanOrEqual(1, $template->version);
            $this->assertNotEmpty($content['categories'] ?? [], "У '{$template->preset_key}' нет категорий работ");
            $this->assertNotEmpty($content['productCategories'] ?? [], "У '{$template->preset_key}' нет категорий товаров");
            $this->assertNotEmpty($content['models'] ?? [], "У '{$template->preset_key}' нет моделей");

            foreach ($content['categories'] as $category) {
                $this->assertNotEmpty($category['key'] ?? null);
                $this->assertNotEmpty($category['name'] ?? null);
                $this->assertNotEmpty($category['services'] ?? [], "Категория '{$category['name']}' пуста");

                foreach ($category['services'] as $service) {
                    // Цены — целые рубли (задача 2.3), клиент кладёт их в `services.price`.
                    $this->assertIsInt($service['price'], "У услуги '{$service['name']}' цена не число");
                    $this->assertGreaterThan(0, $service['price']);
                }
            }
        }
    }

    public function test_seeder_is_idempotent_and_refreshes_existing_content(): void
    {
        // «Старый» контент: как будто в БД лежит устаревшая версия пресета.
        SpecializationTemplate::create([
            'preset_key' => 'bike',
            'version' => 99,
            'content' => ['categories' => [], 'productCategories' => [], 'models' => []],
        ]);

        $this->seed(SpecializationTemplateSeeder::class);
        $this->seed(SpecializationTemplateSeeder::class);

        // Дублей нет, а контент приехал из репозитория — правка без релиза клиента.
        $this->assertSame(4, SpecializationTemplate::count());
        $this->assertSame(1, SpecializationTemplate::where('preset_key', 'bike')->value('version'));

        $bike = SpecializationTemplate::where('preset_key', 'bike')->firstOrFail();
        $this->assertNotEmpty($bike->content['categories']);
        $this->assertSame('Колёса', $bike->content['categories'][0]['name']);
    }

    public function test_database_seeder_also_seeds_presets(): void
    {
        // Пустая точка входа (`DatabaseSeeder`) раньше ничего не делала: `db:seed`
        // на чистой БД оставлял endpoint без контента.
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(4, SpecializationTemplate::count());
    }

    public function test_templates_endpoint_returns_four_seeded_presets(): void
    {
        $this->seed(SpecializationTemplateSeeder::class);

        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/specialization-templates')
            ->assertOk()
            ->assertJsonCount(4, 'templates');

        $keys = array_column($response->json('templates'), 'preset_key');
        sort($keys);
        $this->assertSame(self::PRESET_KEYS, $keys);

        $this->assertSame(
            'Колёса',
            $response->json('templates.2.content.categories.0.name'),
            'Порядок выдачи — по preset_key: aquarium, auto, bike, hvac',
        );
    }
}
