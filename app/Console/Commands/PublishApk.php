<?php

namespace App\Console\Commands;

use App\Repositories\ApkReleaseRepository;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Публикация сборки Android (Фаза 13, задача 13.1).
 *
 * Одна команда вместо «положить файл руками в storage/app/public»: проверяет, что
 * это APK, считает sha256 (клиент сверяет его перед установкой), кладёт файл в
 * каталог релизов и обновляет `releases.json`, из которого `/api/app-version`
 * отдаёт версию приложению.
 *
 * Пример (dev-контур, задача 13.4):
 *   php artisan app:publish-apk storage/app/releases/app-release.apk \
 *     --version-code=2 --version-name=1.1 --notes="Чиним склад"
 */
class PublishApk extends Command
{
    protected $signature = 'app:publish-apk
        {file : путь к собранному APK}
        {--version-code= : versionCode сборки (по умолчанию попробуем прочитать из APK через aapt)}
        {--version-name= : версия «для человека», например 1.1}
        {--min-version=0 : минимальный versionCode, который ещё поддерживается}
        {--mandatory : обновление обязательное (в приложении не будет «позже»)}
        {--notes= : что нового — уезжает в манифест и показывается в приложении}';

    protected $description = 'Публикует APK: считает sha256, кладёт в каталог релизов и обновляет releases.json';

    public function handle(ApkReleaseRepository $releases): int
    {
        $path = (string) $this->argument('file');
        $versionCode = $this->option('version-code');

        if ($versionCode === null || $versionCode === '') {
            $versionCode = $this->detectVersionCodeFromApk($path);
        }

        if ($versionCode === null || (int) $versionCode <= 0) {
            $this->error('Не удалось определить versionCode — передайте --version-code=N (его печатает gradle: APP_VERSION_CODE).');

            return self::FAILURE;
        }

        try {
            $release = $releases->publish($path, [
                'versionCode' => (int) $versionCode,
                'versionName' => (string) ($this->option('version-name') ?? ''),
                'mandatory' => (bool) $this->option('mandatory'),
                'minSupportedVersionCode' => (int) $this->option('min-version'),
                'notes' => (string) ($this->option('notes') ?? ''),
            ]);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Опубликован релиз {$release['versionName']} (versionCode {$release['versionCode']})");
        $this->line('  файл:   '.$releases->directory().DIRECTORY_SEPARATOR.$release['fileName']);
        $this->line('  sha256: '.$release['sha256']);
        $this->line('  размер: '.$release['sizeBytes'].' байт');

        if (! ($release['signed'] ?? false)) {
            $this->warn('APK не подписан (нет META-INF/*.RSA|SF и APK Signing Block): Android не установит такое обновление.');
        }

        $this->line('Проверка: curl '.rtrim((string) config('app.url'), '/').'/api/app-version');

        return self::SUCCESS;
    }

    /**
     * versionCode из APK — через `aapt`, если он есть в PATH. На VPS обычно нет,
     * поэтому основной путь — явный `--version-code` из сборки (gradle.properties).
     */
    private function detectVersionCodeFromApk(string $path): ?int
    {
        if (! is_file($path)) {
            return null;
        }

        $found = [];
        $exitCode = 1;
        @exec('command -v aapt', $found, $exitCode);

        if ($exitCode !== 0) {
            return null;
        }

        $output = [];
        @exec('aapt dump badging '.escapeshellarg($path), $output, $exitCode);

        if ($exitCode !== 0) {
            return null;
        }

        foreach ($output as $line) {
            if (preg_match("/versionCode='(\d+)'/", $line, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return null;
    }
}
