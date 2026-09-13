<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

/**
 * Релизы Android-приложения (Фаза 13, задача 13.1).
 *
 * До Фазы 13 «последнюю» сборку искали на глазок: `AppVersionController` брал
 * первый файл из `glob()` (порядок файловой системы), а скачивание сортировало те
 * же файлы по `filemtime` — версия и фактический файл могли не совпадать, а
 * `versionCode` (именно по нему Android решает, обновляться ли) узнать было неоткуда.
 *
 * Теперь источник правды — манифест `releases.json` рядом с APK: его пишет
 * `php artisan app:publish-apk` (`App\Console\Commands\PublishApk`), а контроллер
 * только читает. Каталог берётся из `config('app-versions.*')`, поэтому тесты
 * подменяют его через `config()` и не трогают реальный storage.
 */
class ApkReleaseRepository
{
    public const MANIFEST_NAME = 'releases.json';

    /** Каталог релизов: APK + `releases.json`. */
    public function directory(): string
    {
        return (string) config('app-versions.directory');
    }

    /** Каталог, куда APK клали до появления манифеста (`storage/app/public/*.apk`). */
    public function legacyDirectory(): string
    {
        return (string) config('app-versions.legacy_directory');
    }

    public function manifestPath(): string
    {
        return $this->directory().DIRECTORY_SEPARATOR.self::MANIFEST_NAME;
    }

    /**
     * Все релизы из манифеста, новые первыми.
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
        $releases = is_array($decoded) ? ($decoded['releases'] ?? []) : [];

        if (! is_array($releases)) {
            return [];
        }

        $releases = array_values(array_filter(
            $releases,
            static fn ($release) => is_array($release) && isset($release['versionCode'])
        ));

        usort($releases, static fn (array $a, array $b) => (int) $b['versionCode'] <=> (int) $a['versionCode']);

        return $releases;
    }

    /**
     * Самый свежий релиз (по `versionCode`).
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
    public function findByVersionCode(int $versionCode): ?array
    {
        foreach ($this->all() as $release) {
            if ((int) $release['versionCode'] === $versionCode) {
                return $release;
            }
        }

        return null;
    }

    /**
     * Путь к APK релиза (`null` — файла на диске нет).
     *
     * @param array<string, mixed> $release
     */
    public function filePath(array $release): ?string
    {
        $fileName = $release['fileName'] ?? null;

        if (! is_string($fileName) || $fileName === '') {
            return null;
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.basename($fileName);

        return File::exists($path) ? $path : null;
    }

    /**
     * Самый свежий APK в легаси-каталоге (`filemtime`), пока манифеста ещё нет.
     * Раньше эта сортировка была только в скачивании — теперь общая.
     */
    public function legacyLatest(): ?string
    {
        $files = glob($this->legacyDirectory().DIRECTORY_SEPARATOR.'*.apk') ?: [];

        if ($files === []) {
            return null;
        }

        usort($files, static fn (string $a, string $b) => filemtime($b) <=> filemtime($a));

        return $files[0];
    }

    /**
     * Публикует APK: проверяет, что это действительно APK, считает sha256 и
     * переписывает манифест. Релиз с тем же `versionCode` заменяется — повторная
     * публикация той же сборки не размножает записи и не ломает уже выданные ссылки.
     *
     * @param array{versionCode:int, versionName?:string, mandatory?:bool, minSupportedVersionCode?:int, notes?:string} $meta
     * @return array<string, mixed> запись релиза
     */
    public function publish(string $sourcePath, array $meta): array
    {
        if (! File::exists($sourcePath)) {
            throw new RuntimeException("APK не найден: {$sourcePath}");
        }

        $versionCode = (int) ($meta['versionCode'] ?? 0);

        if ($versionCode <= 0) {
            throw new RuntimeException('versionCode должен быть положительным целым числом');
        }

        $inspection = $this->inspectApk($sourcePath);

        if (! $inspection['isApk']) {
            throw new RuntimeException("Это не APK (внутри нет AndroidManifest.xml): {$sourcePath}");
        }

        $versionName = (string) ($meta['versionName'] ?? '');

        if ($versionName === '') {
            $versionName = (string) $versionCode;
        }

        File::ensureDirectoryExists($this->directory());

        $fileName = $this->releaseFileName($versionCode, $versionName);
        $targetPath = $this->directory().DIRECTORY_SEPARATOR.$fileName;

        if (realpath($sourcePath) !== realpath($targetPath)) {
            File::copy($sourcePath, $targetPath);
        }

        $release = [
            'versionCode' => $versionCode,
            'versionName' => $versionName,
            'fileName' => $fileName,
            'sha256' => (string) hash_file('sha256', $targetPath),
            'sizeBytes' => (int) File::size($targetPath),
            'mandatory' => (bool) ($meta['mandatory'] ?? false),
            'minSupportedVersionCode' => max(0, (int) ($meta['minSupportedVersionCode'] ?? 0)),
            'notes' => (string) ($meta['notes'] ?? ''),
            // Подпись важна: неподписанный APK Android установить поверх не даст.
            'signed' => $inspection['signed'],
            'releasedAt' => now()->toIso8601String(),
        ];

        $others = array_filter(
            $this->all(),
            static fn (array $existing) => (int) $existing['versionCode'] !== $versionCode
        );

        $this->writeManifest(array_merge(array_values($others), [$release]));

        return $release;
    }

    /**
     * @param array<int, array<string, mixed>> $releases
     */
    public function writeManifest(array $releases): void
    {
        usort($releases, static fn (array $a, array $b) => (int) $b['versionCode'] <=> (int) $a['versionCode']);

        File::ensureDirectoryExists($this->directory());

        File::put(
            $this->manifestPath(),
            (string) json_encode(
                ['releases' => array_values($releases)],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );
    }

    /** Имя файла релиза: `<versionCode>-<versionName>.apk` — без пробелов и слешей. */
    public function releaseFileName(int $versionCode, string $versionName): string
    {
        $safeName = trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '-', $versionName), '-');

        return $versionCode.'-'.($safeName === '' ? 'release' : $safeName).'.apk';
    }

    /**
     * Проверка APK: ZIP-контейнер + `AndroidManifest.xml` + признак подписи
     * (v1 `META-INF/*.RSA|DSA|EC|SF` либо APK Signing Block v2+).
     *
     * @return array{isApk:bool, signed:bool}
     */
    private function inspectApk(string $path): array
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Не удалось прочитать APK: {$path}");
        }

        $magic = (string) fread($handle, 4);
        fclose($handle);

        if ($magic !== "PK\x03\x04") {
            return ['isApk' => false, 'signed' => false];
        }

        if (! class_exists(ZipArchive::class)) {
            // Без расширения zip остаётся проверка «это ZIP» — для публикации хватит.
            return ['isApk' => true, 'signed' => $this->hasApkSigningBlock($path)];
        }

        $zip = new ZipArchive();
        $hasManifest = false;
        $hasV1Signature = false;

        if ($zip->open($path) === true) {
            $hasManifest = $zip->locateName('AndroidManifest.xml') !== false;

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = (string) $zip->getNameIndex($index);

                if (str_starts_with($entry, 'META-INF/') && preg_match('/\.(RSA|DSA|EC|SF)$/i', $entry) === 1) {
                    $hasV1Signature = true;
                    break;
                }
            }

            $zip->close();
        }

        return [
            'isApk' => $hasManifest,
            'signed' => $hasV1Signature || $this->hasApkSigningBlock($path),
        ];
    }

    /** APK Signing Block (v2/v3): магическая строка лежит в конце файла. */
    private function hasApkSigningBlock(string $path): bool
    {
        $size = (int) File::size($path);
        $tailLength = min($size, 1024 * 1024);

        if ($tailLength <= 0) {
            return false;
        }

        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        fseek($handle, $size - $tailLength);
        $tail = (string) fread($handle, $tailLength);
        fclose($handle);

        return str_contains($tail, 'APK Sig Block 42');
    }
}


