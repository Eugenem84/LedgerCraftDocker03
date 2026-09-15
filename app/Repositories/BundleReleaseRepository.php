<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

/**
 * OTA-бандлы веб-слоя (Фаза 15, задача 15.7).
 *
 * Бандл — это zip с веб-сборкой приложения (`src-capacitor/www`). Клиент скачивает его,
 * сверяет sha256 (**в base64** — так его ждёт плагин OTA) и применяет при следующем
 * запуске: обновление интерфейса и логики без установки APK.
 *
 * Источник правды — манифест `bundles.json` рядом с zip: его пишет
 * `php artisan app:publish-bundle` (`App\Console\Commands\PublishBundle`), а контроллер
 * `/api/app-version` только читает и отдаёт полем `bundle`.
 *
 * Почему отдельный манифест, а не запись в `releases.json`: это независимые артефакты.
 * Новый APK и новый бандл выходят в разное время, и публикация бандла не должна трогать
 * релиз APK (и наоборот). Клиент сам решает, что ему доступно, — APK важнее, а бандл
 * применяется, если `minNativeVersionCode` не выше установленного `versionCode`.
 */
class BundleReleaseRepository
{
    public const MANIFEST_NAME = 'bundles.json';

    /** Каталог бандлов: zip + `bundles.json`. */
    public function directory(): string
    {
        return (string) config('app-versions.bundles_directory');
    }

    public function manifestPath(): string
    {
        return $this->directory().DIRECTORY_SEPARATOR.self::MANIFEST_NAME;
    }

    /**
     * Все бандлы из манифеста, новые первыми.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $path = $this->manifestPath();

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);
        $bundles = is_array($decoded) ? ($decoded['bundles'] ?? []) : [];

        if (! is_array($bundles)) {
            return [];
        }

        $bundles = array_values(array_filter(
            $bundles,
            static fn ($bundle) => is_array($bundle) && isset($bundle['version'], $bundle['publishedAt'])
        ));

        // «Новее» у бандлов — дата публикации: идентификатор версии свободный (строка),
        // поэтому порядок задаёт время, а не сравнение строк.
        usort(
            $bundles,
            static fn (array $a, array $b) => strcmp((string) $b['publishedAt'], (string) $a['publishedAt'])
        );

        return $bundles;
    }

    /**
     * Самый свежий бандл.
     *
     * @return array<string, mixed>|null
     */
    public function latest(): ?array
    {
        return $this->all()[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByVersion(string $version): ?array
    {
        foreach ($this->all() as $bundle) {
            if ((string) $bundle['version'] === $version) {
                return $bundle;
            }
        }

        return null;
    }

    /**
     * Путь к zip бандла (`null` — файла на диске нет).
     *
     * @param array<string, mixed> $bundle
     */
    public function filePath(array $bundle): ?string
    {
        $fileName = $bundle['fileName'] ?? null;

        if (! is_string($fileName) || $fileName === '') {
            return null;
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.basename($fileName);

        return File::exists($path) ? $path : null;
    }

    /**
     * Публикует бандл: проверяет zip, считает хэши и переписывает манифест.
     * Бандл с тем же `version` заменяется — повторная публикация не размножает записи.
     *
     * @param array{version:string, minNativeVersionCode?:int, notes?:string, checksum?:string} $meta
     * @return array<string, mixed> запись бандла
     */
    public function publish(string $sourcePath, array $meta): array
    {
        if (! File::exists($sourcePath)) {
            throw new RuntimeException("Бандл не найден: {$sourcePath}");
        }

        $version = trim((string) ($meta['version'] ?? ''));

        if ($version === '') {
            throw new RuntimeException('version бандла обязателен (например 1.11.260915-1210)');
        }

        if (! $this->containsIndexHtml($sourcePath)) {
            throw new RuntimeException(
                "Это не бандл веб-слоя: в корне zip должен лежать index.html ({$sourcePath})"
            );
        }

        File::ensureDirectoryExists($this->directory());

        $fileName = $this->bundleFileName($version);
        $targetPath = $this->directory().DIRECTORY_SEPARATOR.$fileName;

        if (realpath($sourcePath) !== realpath($targetPath)) {
            File::copy($sourcePath, $targetPath);
        }

        $checksumHex = $this->checksumHex($targetPath);
        $checksumBase64 = $this->checksumBase64($targetPath);
        $passedChecksum = trim((string) ($meta['checksum'] ?? ''));

        // Хэш считаем сами, «на веру» переданное значение не берём: клиент применяет бандл
        // только при совпадении, поэтому доверчивость означала бы битое обновление у мастера.
        // Принимаем и hex, и base64 — скрипт релиза печатает оба, а сравнивает клиент hex.
        if (
            $passedChecksum !== ''
            && $passedChecksum !== $checksumHex
            && $passedChecksum !== $checksumBase64
        ) {
            throw new RuntimeException(
                'Переданный --checksum не совпал с файлом: ожидали '.$checksumHex.', получили '.$passedChecksum
            );
        }

        $bundle = [
            'version' => $version,
            'fileName' => $fileName,
            /*
             * ⚠️ Именно **hex**: плагин OTA на клиенте (`@capawesome/capacitor-live-update`)
             * считает sha256 скачанного zip и приводит его к hex (`getChecksumForFileAsString`),
             * а затем сравнивает строки. Значение из документации плагина («base64») живой прогон
             * отверг ошибкой `Checksum mismatch` (отчёт мастера №5, 15.09.2026) — base64 лежит
             * рядом отдельным полем `checksumBase64`, только для справки.
             */
            'checksum' => $checksumHex,
            'checksumBase64' => $checksumBase64,
            'sha256' => $checksumHex,
            'sizeBytes' => (int) File::size($targetPath),
            'minNativeVersionCode' => max(0, (int) ($meta['minNativeVersionCode'] ?? 0)),
            'notes' => (string) ($meta['notes'] ?? ''),
            'publishedAt' => now()->toIso8601String(),
        ];

        $others = array_filter(
            $this->all(),
            static fn (array $existing) => (string) $existing['version'] !== $version
        );

        $this->writeManifest(array_merge(array_values($others), [$bundle]));

        return $bundle;
    }

    /**
     * @param array<int, array<string, mixed>> $bundles
     */
    public function writeManifest(array $bundles): void
    {
        File::ensureDirectoryExists($this->directory());

        File::put(
            $this->manifestPath(),
            (string) json_encode(
                ['bundles' => array_values($bundles)],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );
    }

    /** Имя файла бандла: без пробелов и слешей (идентификатор приходит от скрипта релиза). */
    public function bundleFileName(string $version): string
    {
        $safeVersion = trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '-', $version), '-');

        return 'bundle-'.($safeVersion === '' ? 'latest' : $safeVersion).'.zip';
    }

    /**
     * sha256 файла в **hex** — именно это значение сравнивает плагин OTA на клиенте
     * (он считает sha256 скачанного zip и приводит его к hex), поэтому оно уходит
     * в манифест полем `checksum`.
     */
    public function checksumHex(string $path): string
    {
        return (string) hash_file('sha256', $path);
    }

    /** Тот же хэш в base64 — только для справки (в манифесте отдельным полем). */
    public function checksumBase64(string $path): string
    {
        $hash = hash_file('sha256', $path, true);

        if ($hash === false) {
            throw new RuntimeException("Не удалось посчитать sha256: {$path}");
        }

        return base64_encode($hash);
    }

    /** Проверка бандла: это zip и в его корне есть `index.html` (его отдаёт веб-сервер). */
    private function containsIndexHtml(string $path): bool
    {
        if (! class_exists(ZipArchive::class)) {
            // Без расширения zip остаётся проверка «это похоже на zip» — как у APK.
            $handle = @fopen($path, 'rb');

            if ($handle === false) {
                return false;
            }

            $magic = (string) fread($handle, 4);
            fclose($handle);

            return $magic === "PK\x03\x04";
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return false;
        }

        $hasIndex = $zip->locateName('index.html') !== false;
        $zip->close();

        return $hasIndex;
    }
}
