<?php

namespace App\Console\Commands;

use App\Models\FeedbackReport;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Выгрузка отчётов «Сообщить об ошибке» в файл (Фаза 14, задача 14.6).
 *
 * Основной путь выгрузки — `GET /api/feedback` + `npm run feedback:pull` на стороне
 * клиента (файлы ложатся прямо в `feedback/INBOX.md`). Эта команда — запасной путь
 * «по SSH»: когда HTTPS-выгрузку трогать не хочется, отчёты достаются файлами из
 * `storage/app/feedback/` и переносятся в инбокс вручную.
 */
class ExportFeedback extends Command
{
    protected $signature = 'feedback:export {--since=} {--md} {--out=}';

    protected $description = 'Выгружает отчёты «Сообщить об ошибке» в файл (Фаза 14)';

    public function handle(): int
    {
        $query = FeedbackReport::query()->orderBy('id');

        if ($since = $this->option('since')) {
            try {
                $query->where('created_at', '>=', Carbon::parse($since));
            } catch (\Throwable $e) {
                $this->error("Не разобрать дату --since=\"{$since}\"");

                return self::FAILURE;
            }
        }

        $reports = $query->get();
        $directory = $this->option('out') ?: (string) config('feedback.export_directory');
        $stamp = now()->format('Y-m-d_H-i-s');

        File::ensureDirectoryExists($directory);

        $records = $reports->map(fn (FeedbackReport $report) => [
            'server_id'  => $report->id,
            'uuid_id'    => $report->uuid_id,
            'user_id'    => $report->user_id,
            'status'     => $report->status,
            'created_at' => optional($report->created_at)->toJSON(),
            'payload'    => $report->payload ?? [],
        ])->values();

        $jsonPath = $directory.DIRECTORY_SEPARATOR."feedback-{$stamp}.json";

        File::put($jsonPath, $records->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Отчётов: {$records->count()} → {$jsonPath}");

        if ($this->option('md')) {
            $mdPath = $directory.DIRECTORY_SEPARATOR."feedback-{$stamp}.md";

            File::put($mdPath, $this->renderDigest($records->all()));
            $this->info("Дайджест → {$mdPath}");
        }

        return self::SUCCESS;
    }

    /**
     * Короткий дайджест: что случилось, откуда и с какой версией. Полный отчёт лежит
     * рядом в JSON — его можно положить в `feedback/inbox/` как есть.
     */
    private function renderDigest(array $records): string
    {
        $lines = [
            '# Отчёты «Сообщить об ошибке»',
            '',
            'Выгружено: '.now()->toDateTimeString(),
            'Отчётов: '.count($records),
            '',
        ];

        foreach ($records as $record) {
            $payload = $record['payload'] ?? [];

            $lines[] = sprintf(
                '- **%s** · %s · %s · %s · %s',
                $payload['uuid_id'] ?? $record['uuid_id'] ?? '—',
                $payload['client_created_at'] ?? $record['created_at'] ?? '—',
                $payload['kind'] ?? '—',
                $payload['app_version'] ?? 'версия неизвестна',
                $this->oneLine($payload['message'] ?? '')
            );
        }

        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    private function oneLine(string $value): string
    {
        $text = preg_replace('/\s+/u', ' ', $value) ?? '';

        return mb_strlen($text) > 120 ? mb_substr($text, 0, 119).'…' : $text;
    }
}
