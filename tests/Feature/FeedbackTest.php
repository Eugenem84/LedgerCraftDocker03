<?php

namespace Tests\Feature;

use App\Models\FeedbackReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Отчёты «Сообщить об ошибке» (Фаза 14, задачи 14.4/14.6, решение D7).
 *
 * Проверяем обе стороны контура:
 *   • приём (`POST /api/feedback`): владелец из токена, идемпотентность по `uuid_id`,
 *     валидация, повторная обрезка лимитов и отсутствие «лишних» полей;
 *   • выгрузка (`GET`/`PATCH /api/feedback`): доступ только по pull-токену (обычный
 *     токен мастерской чужие отчёты не отдаёт), фильтр `since`, статус разбора.
 *
 * Тест идёт на отдельной тестовой БД (см. `phpunit.xml`): `RefreshDatabase` сносит
 * таблицы, поэтому на «не тестовой» БД тест пропускается.
 */
class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    private const PULL_TOKEN = 'test-pull-token';

    private User $user;

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || ! str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты отчётов запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();

        config(['feedback.pull_token' => self::PULL_TOKEN]);
        $this->user = User::factory()->create();
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'uuid_id'           => 'b7f1c0de-1111-2222-3333-444455556666',
            'kind'              => 'bug',
            'message'           => 'кнопка «сохранить» ничего не делает',
            'contact'           => 'мастер@example.com',
            'screen'            => '/orders/42',
            'app_version'       => '1.1 (2)',
            'platform'          => 'android',
            'platform_version'  => 'Android 13; Pixel 6a',
            'api_url'           => 'https://dev.example/api',
            'schema_version'    => 30,
            'schema_stored'     => 30,
            'account'           => 'master@example.com',
            'profile'           => 'Велосервис',
            'sync'              => ['online' => false, 'pendingCount' => 3, 'lastError' => 'Network Error'],
            'errors'            => [['time' => '2026-09-13T20:10:59.000Z', 'level' => 'error', 'message' => '[Sync] boom']],
            'logs'              => [],
            'client_created_at' => '2026-09-13T20:11:03.000Z',
        ], $overrides);
    }

    public function test_store_saves_report_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/feedback', $this->payload());

        $response->assertCreated()->assertJson(['ok' => true]);

        $report = FeedbackReport::query()->firstOrFail();

        $this->assertSame($this->user->id, $report->user_id);
        $this->assertSame('new', $report->status);
        $this->assertSame('bug', $report->kind);
        $this->assertSame('кнопка «сохранить» ничего не делает', $report->message);
        $this->assertSame('Велосервис', $report->profile_name);
        $this->assertSame('/orders/42', $report->screen);
        $this->assertSame($response->json('server_id'), $report->id);
        $this->assertSame('b7f1c0de-1111-2222-3333-444455556666', $report->payload['uuid_id']);
        $this->assertCount(1, $report->payload['errors']);
    }

    public function test_store_is_idempotent_by_uuid(): void
    {
        Sanctum::actingAs($this->user);

        $first = $this->postJson('/api/feedback', $this->payload());
        $second = $this->postJson('/api/feedback', $this->payload());

        $first->assertCreated();
        $second->assertOk()->assertJson(['duplicate' => true]);

        $this->assertSame($first->json('server_id'), $second->json('server_id'));
        $this->assertSame(1, FeedbackReport::query()->count());
    }

    public function test_store_validates_fields(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/feedback', $this->payload(['message' => 'ok']))->assertStatus(422);
        $this->postJson('/api/feedback', $this->payload(['kind' => 'magic']))->assertStatus(422);
        $this->postJson('/api/feedback', $this->payload(['uuid_id' => '']))->assertStatus(422);
        $this->assertSame(0, FeedbackReport::query()->count());
    }

    public function test_store_requires_auth(): void
    {
        $this->postJson('/api/feedback', $this->payload())->assertUnauthorized();
        $this->assertSame(0, FeedbackReport::query()->count());
    }

    public function test_store_clips_limits_and_drops_unknown_fields(): void
    {
        Sanctum::actingAs($this->user);

        $errors = [];

        for ($index = 0; $index < 120; $index++) {
            $errors[] = ['time' => '', 'level' => 'error', 'message' => "ошибка {$index}"];
        }

        $response = $this->postJson('/api/feedback', $this->payload([
            'errors'      => $errors,
            'client_name' => 'Иван',
            'order_total' => 12345,
        ]));

        $response->assertCreated();

        $payload = FeedbackReport::query()->firstOrFail()->payload;

        $this->assertCount(50, $payload['errors']);
        $this->assertSame('ошибка 119', $payload['errors'][49]['message']);
        // Данные мастерской отбрасываются: их нет ни в payload, ни в колонках.
        $this->assertArrayNotHasKey('client_name', $payload);
        $this->assertArrayNotHasKey('order_total', $payload);
    }

    public function test_index_requires_pull_token(): void
    {
        FeedbackReport::query()->create([
            'uuid_id'  => 'report-1',
            'kind'     => 'bug',
            'message'  => 'текст',
            'payload'  => ['uuid_id' => 'report-1', 'message' => 'текст'],
            'status'   => 'new',
        ]);

        $this->getJson('/api/feedback')->assertForbidden();
        $this->getJson('/api/feedback', ['X-Feedback-Token' => 'wrong'])->assertForbidden();

        // Пользовательский bearer сам по себе чужие отчёты не отдаёт: ручка вне sanctum.
        Sanctum::actingAs($this->user);
        $this->getJson('/api/feedback')->assertForbidden();
    }

    public function test_index_returns_reports_and_filters_by_since(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/feedback', $this->payload())->assertCreated();
        $this->postJson('/api/feedback', $this->payload([
            'uuid_id' => 'b7f1c0de-9999-2222-3333-444455556666',
        ]))->assertCreated();

        $response = $this->getJson('/api/feedback?limit=10', ['X-Feedback-Token' => self::PULL_TOKEN]);

        $response->assertOk()->assertJson(['count' => 2]);

        $report = $response->json('reports.0');
        $this->assertArrayHasKey('payload', $report);
        $this->assertSame('bug', $report['payload']['kind']);
        $this->assertSame('new', $report['status']);

        // Курсор выгрузки: отчёты «из будущего» не попадают.
        $future = now()->addDay()->toJSON();
        $this->getJson('/api/feedback?since='.urlencode($future), ['X-Feedback-Token' => self::PULL_TOKEN])
            ->assertOk()
            ->assertJson(['count' => 0]);

        $this->getJson('/api/feedback?status=magic', ['X-Feedback-Token' => self::PULL_TOKEN])
            ->assertStatus(400);
    }

    public function test_update_sets_review_status(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/feedback', $this->payload())->assertCreated();

        $uuid = $this->payload()['uuid_id'];

        $this->patchJson(
            "/api/feedback/{$uuid}",
            ['status' => 'accepted', 'note' => 'заведено задачей 14.5'],
            ['X-Feedback-Token' => self::PULL_TOKEN]
        )->assertOk()->assertJson(['status' => 'accepted']);

        $report = FeedbackReport::query()->where('uuid_id', $uuid)->firstOrFail();

        $this->assertSame('accepted', $report->status);
        $this->assertSame('заведено задачей 14.5', $report->resolution_note);

        $this->patchJson("/api/feedback/unknown-id", ['status' => 'read'], ['X-Feedback-Token' => self::PULL_TOKEN])
            ->assertNotFound();

        $this->patchJson("/api/feedback/{$uuid}", ['status' => 'magic'], ['X-Feedback-Token' => self::PULL_TOKEN])
            ->assertStatus(422);
    }

    public function test_export_command_writes_json_and_digest(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/feedback', $this->payload())->assertCreated();

        $directory = storage_path('app/feedback-test-'.uniqid());

        try {
            $this->artisan('feedback:export', ['--md' => true, '--out' => $directory])->assertSuccessful();

            $files = File::files($directory);

            $this->assertCount(2, $files);

            $json = json_decode(File::get(collect($files)->first(fn ($file) => $file->getExtension() === 'json')), true);

            $this->assertCount(1, $json);
            $this->assertSame('bug', $json[0]['payload']['kind']);

            $digest = File::get(collect($files)->first(fn ($file) => $file->getExtension() === 'md'));

            $this->assertStringContainsString('кнопка «сохранить» ничего не делает', $digest);
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
