<?php

namespace App\Console\Commands;

use App\Repositories\BundleReleaseRepository;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Публикация OTA-бандла веб-слоя (Фаза 15, задача 15.7).
 *
 * Одна команда вместо «положить zip руками в storage»: проверяет, что это именно бандл
 * (в корне архива `index.html`), считает sha256 в base64 (клиент сверяет его плагином
 * OTA и не применяет битый файл) и обновляет `bundles.json`, из которого
 * `/api/app-version` отдаёт бандл полем `bundle`.
 *
 * Пример (его печатает `npm run release:web` на клиенте):
 *   php artisan app:publish-bundle storage/app/bundles/bundle-1.11.260915-1210.zip \
 *     --bundle-version=1.11.260915-1210 --checksum=<sha256 в base64> --min-native-version=12 \
 *     --notes="Правки склада"
 *
 * ⚠️ Имя опции — `--bundle-version`, а **не** `--version`: `--version` у Symfony Console
 * глобальный (печатает версию фреймворка и выходит, не доходя до команды), поэтому
 * публикация молча ничего не делала.
 */
class PublishBundle extends Command
{
    protected $signature = 'app:publish-bundle
        {file : путь к собранному zip с веб-сборкой}
        {--bundle-version= : идентификатор бандла (строка, например 1.11.260915-1210)}
        {--checksum= : sha256 файла в base64 — сверяется с посчитанным на сервере}
        {--min-native-version=0 : минимальный versionCode APK, на котором бандл имеет смысл}
        {--notes= : что нового — уезжает в манифест и показывается в приложении}';

    protected $description = 'Публикует OTA-бандл веб-слоя: проверяет zip, считает sha256 и обновляет bundles.json';

    public function handle(BundleReleaseRepository $bundles): int
    {
        $path = (string) $this->argument('file');
        $version = trim((string) ($this->option('bundle-version') ?? ''));

        if ($version === '') {
            $this->error('Укажите --bundle-version=<идентификатор бандла>: его печатает npm run release:web.');

            return self::FAILURE;
        }

        try {
            $bundle = $bundles->publish($path, [
                'version' => $version,
                'checksum' => (string) ($this->option('checksum') ?? ''),
                'minNativeVersionCode' => (int) $this->option('min-native-version'),
                'notes' => (string) ($this->option('notes') ?? ''),
            ]);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Опубликован бандл {$bundle['version']}");
        $this->line('  файл:   '.$bundles->directory().DIRECTORY_SEPARATOR.$bundle['fileName']);
        $this->line('  sha256: '.$bundle['sha256'].' (hex — это значение сверяет клиент)');
        $this->line('  base64: '.$bundle['checksumBase64'].' (справочно, плагин его не принимает)');
        $this->line('  размер: '.$bundle['sizeBytes'].' байт');
        $this->line('  минимальный versionCode APK: '.$bundle['minNativeVersionCode']);

        $this->line('Проверка: curl '.rtrim((string) config('app.url'), '/').'/api/app-version');

        return self::SUCCESS;
    }
}
