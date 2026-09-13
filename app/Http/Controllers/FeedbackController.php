<?php

namespace App\Http\Controllers;

use App\Models\FeedbackReport;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Приём и выгрузка отчётов «Сообщить об ошибке» (Фаза 14, задачи 14.4/14.6, решение D7).
 *
 * Контур отдельный от синка: своя таблица, своя ручка, своя идемпотентность.
 *   • `POST /api/feedback` (auth:sanctum + throttle) — принимает отчёт с устройства,
 *     ищет повтор по `uuid_id` и отдаёт `server_id` уже принятого отчёта;
 *   • `GET /api/feedback` (pull-токен) — выгрузка для разработки и агента: клиент
 *     (`scripts/pull-feedback.mjs`) раскладывает ответ в `feedback/INBOX.md`;
 *   • `PATCH /api/feedback/{uuid_id}` (pull-токен) — статус разбора отчёта.
 *
 * Данных мастерской здесь нет по построению: принимаем только поля контракта
 * (`docs/FEEDBACK.md` §3), лишнее отбрасываем, длинное обрезаем ещё раз — на случай,
 * если отчёт собрал не наш клиент.
 */
class FeedbackController extends Controller
{
    /** Типы отчёта (те же, что в диалоге клиента). */
    public const KINDS = ['bug', 'suggestion', 'question', 'crash'];

    /** Статусы разбора ставит разработчик/агент, а не клиент. */
    public const STATUSES = ['new', 'read', 'accepted', 'rejected'];

    private const ERRORS_LIMIT = 50;

    private const LOGS_LIMIT = 100;

    private const LINE_LIMIT = 500;

    private const MESSAGE_LIMIT = 4000;

    /** POST /api/feedback — приём отчёта с устройства (идемпотентно по `uuid_id`). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'uuid_id'           => ['required', 'string', 'max:64'],
            'kind'              => ['required', 'string', Rule::in(self::KINDS)],
            'message'           => ['required', 'string', 'min:3', 'max:'.self::MESSAGE_LIMIT],
            'contact'           => ['nullable', 'string', 'max:200'],
            'screen'            => ['nullable', 'string', 'max:200'],
            'app_version'       => ['nullable', 'string', 'max:100'],
            'platform'          => ['nullable', 'string', 'max:32'],
            'platform_version'  => ['nullable', 'string', 'max:200'],
            'device'            => ['nullable', 'string', 'max:100'],
            'api_url'           => ['nullable', 'string', 'max:200'],
            'schema_version'    => ['nullable', 'integer', 'min:0'],
            'schema_stored'     => ['nullable', 'integer', 'min:0'],
            'account'           => ['nullable', 'string', 'max:200'],
            'profile'           => ['nullable', 'string', 'max:200'],
            'sync'              => ['nullable', 'array'],
            'errors'            => ['nullable', 'array', 'max:500'],
            'logs'              => ['nullable', 'array', 'max:500'],
            'client_created_at' => ['nullable', 'date'],
        ]);

        $existing = FeedbackReport::query()->where('uuid_id', $data['uuid_id'])->first();

        if ($existing !== null) {
            return $this->duplicateResponse($existing);
        }

        $payload = $this->buildPayload($data);

        try {
            $report = FeedbackReport::create([
                'uuid_id'           => $data['uuid_id'],
                'user_id'           => $request->user()?->id,
                'kind'              => $data['kind'],
                'message'           => $payload['message'],
                'contact'           => $payload['contact'] ?: null,
                'screen'            => $payload['screen'],
                'app_version'       => $payload['app_version'],
                'platform'          => $payload['platform'],
                'platform_version'  => $payload['platform_version'],
                'device'            => $payload['device'],
                'api_url'           => $payload['api_url'],
                'schema_version'    => $payload['schema_version'],
                'schema_stored'     => $payload['schema_stored'],
                'account_email'     => $payload['account'] ?: null,
                'profile_name'      => $payload['profile'] ?: null,
                'payload'           => $payload,
                'sync'              => $payload['sync'],
                'errors'            => $payload['errors'],
                'logs'              => $payload['logs'],
                'client_created_at' => $data['client_created_at'] ?? null,
                'status'            => 'new',
                'ip'                => $request->ip(),
                'user_agent'        => mb_substr((string) $request->userAgent(), 0, 1000),
            ]);
        } catch (QueryException $e) {
            // Гонка двух одинаковых отправок: победил другой запрос — отдаём его id,
            // дубль не создаём (тот же принцип, что в синке, задача 3.5).
            $winner = FeedbackReport::query()->where('uuid_id', $data['uuid_id'])->first();

            if ($winner !== null) {
                return $this->duplicateResponse($winner);
            }

            throw $e;
        }

        return response()->json([
            'ok'        => true,
            'server_id' => $report->id,
            'uuid_id'   => $report->uuid_id,
        ], 201);
    }

    /** GET /api/feedback — выгрузка отчётов (pull-токен, вне `auth:sanctum`). */
    public function index(Request $request)
    {
        $limit = min(max((int) $request->query('limit', 50), 1), 500);
        $status = $request->query('status');
        $since = $request->query('since');

        if ($status !== null && $status !== '' && ! in_array($status, self::STATUSES, true)) {
            return response()->json(['error' => 'INVALID_STATUS'], 400);
        }

        $query = FeedbackReport::query()->orderBy('id');

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($since !== null && $since !== '') {
            try {
                $query->where('created_at', '>=', Carbon::parse($since));
            } catch (\Throwable $e) {
                return response()->json(['error' => 'INVALID_SINCE'], 400);
            }
        }

        $reports = $query->limit($limit)->get();

        return response()->json([
            'count'   => $reports->count(),
            'reports' => $reports->map(fn (FeedbackReport $report) => [
                'server_id'  => $report->id,
                'uuid_id'    => $report->uuid_id,
                'user_id'    => $report->user_id,
                'status'     => $report->status,
                'created_at' => optional($report->created_at)->toJSON(),
                'payload'    => $report->payload ?? [],
            ])->values(),
        ]);
    }

    /** PATCH /api/feedback/{uuid_id} — статус разбора (чтобы отчёт не заводили дважды). */
    public function update(Request $request, string $uuidId)
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(self::STATUSES)],
            'note'   => ['nullable', 'string', 'max:2000'],
        ]);

        $report = FeedbackReport::query()->where('uuid_id', $uuidId)->first();

        if ($report === null) {
            return response()->json(['error' => 'FEEDBACK_NOT_FOUND'], 404);
        }

        $report->status = $data['status'];
        $report->resolution_note = $data['note'] ?? $report->resolution_note;
        $report->save();

        return response()->json([
            'ok'        => true,
            'server_id' => $report->id,
            'uuid_id'   => $report->uuid_id,
            'status'    => $report->status,
        ]);
    }

    /** Ответ на повтор: тот же отчёт, дубль не создаём. */
    private function duplicateResponse(FeedbackReport $report)
    {
        return response()->json([
            'ok'        => true,
            'server_id' => $report->id,
            'uuid_id'   => $report->uuid_id,
            'duplicate' => true,
        ]);
    }

    /** Отчёт в том виде, в каком его прислал клиент (лишнее отброшено, длинное обрезано). */
    private function buildPayload(array $data): array
    {
        return [
            'uuid_id'           => $data['uuid_id'],
            'kind'              => $data['kind'],
            'message'           => $this->clip($data['message'], self::MESSAGE_LIMIT),
            'contact'           => $this->clip($data['contact'] ?? '', 200),
            'screen'            => $this->clip($data['screen'] ?? '', 200) ?: null,
            'app_version'       => $data['app_version'] ?? null,
            'platform'          => $data['platform'] ?? null,
            'platform_version'  => $data['platform_version'] ?? null,
            'device'            => $data['device'] ?? null,
            'api_url'           => $data['api_url'] ?? null,
            'schema_version'    => $data['schema_version'] ?? null,
            'schema_stored'     => $data['schema_stored'] ?? null,
            'account'           => $data['account'] ?? '',
            'profile'           => $data['profile'] ?? '',
            'sync'              => $data['sync'] ?? null,
            'errors'            => $this->clipEntries($data['errors'] ?? [], self::ERRORS_LIMIT),
            'logs'              => $this->clipEntries($data['logs'] ?? [], self::LOGS_LIMIT),
            'client_created_at' => $data['client_created_at'] ?? null,
        ];
    }

    /** Хвост буфера: последние `$limit` записей, у каждой обрезано сообщение. */
    private function clipEntries(array $entries, int $limit): array
    {
        $clipped = [];

        foreach (array_slice($entries, -$limit) as $entry) {
            if (! is_array($entry) || ! isset($entry['message'])) {
                continue;
            }

            $level = in_array($entry['level'] ?? null, ['log', 'table', 'warn', 'error'], true)
                ? $entry['level']
                : 'error';

            $entryClipped = [
                'time'    => isset($entry['time']) ? $this->clip((string) $entry['time'], 40) : '',
                'level'   => $level,
                'message' => $this->clip((string) $entry['message'], self::LINE_LIMIT),
            ];

            if (isset($entry['screen'])) {
                $entryClipped['screen'] = $this->clip((string) $entry['screen'], 200);
            }

            $clipped[] = $entryClipped;
        }

        return $clipped;
    }

    private function clip(string $value, int $limit): string
    {
        return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit - 1).'…' : $value;
    }
}
