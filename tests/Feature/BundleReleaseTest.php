<?php

namespace Tests\Feature;

use App\Repositories\ApkReleaseRepository;
use App\Repositories\BundleReleaseRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

/**
 * OTA-бандлы веб-слоя — Фаза 15, задача 15.7.
 *
 * Критерий: `/api/app-version` отдаёт `bundle` (версия, ссылка, sha256 **в base64**,
 * размер, `minNativeVersionCode`), а скачанный zip совпадает по хэшу с опубликованным.
 * Плюс защита от публикации мусора: не-zip, zip без `index.html` в корне, чужой checksum.
 *
 * Тесты не ходят в БД (бандлы — это файлы), поэтому каталоги подменяются через
 * `config('app-versions.*')`, как в `AppVersionTest`.
 */
class BundleReleaseTest extends TestCase
{
    private string $baseDir;

    private string $releasesDir;

    private string $bundlesDir;

    private string $sourceDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->baseDir = storage_path('framework/testing/app-bundles-'.uniqid());
        $this->releasesDir = $this->baseDir.'/releases';
        $this->bundlesDir = $this->baseDir.'/bundles';
        $this->sourceDir = $this->baseDir.'/sources';

        foreach ([$this->releasesDir, $this->bundlesDir, $this->sourceDir] as $dir) {
            File::ensureDirectoryExists($dir);
        }

        Config::set('app-versions.directory', $this->releasesDir);
        Config::set('app-versions.bundles_directory', $this->bundlesDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->baseDir);

        parent::tearDown();
    }

    public function test_without_a_bundle_the_response_has_no_bundle_key(): void
    {
        $this->publishRelease(12, '1.11');

        $this->getJson('/api/app-version')
            ->assertOk()
            ->assertJsonMissing(['bundle'])
            ->assertJson(['versionCode' => 12, 'versionName' => '1.11']);
    }

    public function test_published_bundle_appears_in_the_version_response(): void
    {
        $this->publishRelease(12, '1.11');
        $zip = $this->publishBundle('1.11.260915-1210', ['--min-native-version' => 12, '--notes' => 'Правки склада']);

        // Плагин OTA сравнивает **hex**-хэш скачанного zip, поэтому в манифесте — hex
        // (base64 живёт отдельным полем, только для справки: плагин его не принимает).
        $expectedChecksum = (string) hash_file('sha256', $zip);
        $expectedBase64 = base64_encode($expectedChecksum === '' ? '' : (string) hash_file('sha256', $zip, true));

        $response = $this->getJson('/api/app-version')->assertOk();

        $response
            ->assertJsonPath('bundle.version', '1.11.260915-1210')
            ->assertJsonPath('bundle.checksum', $expectedChecksum)
            ->assertJsonPath('bundle.checksumBase64', $expectedBase64)
            ->assertJsonPath('bundle.minNativeVersionCode', 12)
            ->assertJsonPath('bundle.notes', 'Правки склада')
            ->assertJsonPath('bundle.sizeBytes', (int) File::size($zip));

        // Ссылка ведёт на раздачу бандла с идентификатором версии.
        $this->assertStringContainsString(
            '/api/download-bundle?version=1.11.260915-1210',
            (string) $response->json('bundle.url')
        );
    }

    /** Публикация релиза APK — манифест версии должен существовать (как в AppVersionTest). */
    private function publishRelease(int $versionCode, string $versionName): void
    {
        $apk = $this->sourceDir."/app-{$versionCode}-{$versionName}.apk";
        $this->makeApk($apk);

        $this->artisan('app:publish-apk', [
            'file' => $apk,
            '--version-code' => $versionCode,
            '--version-name' => $versionName,
        ])->assertExitCode(0);
    }

    /** Публикация бандла через artisan — как это делает `npm run release:web` на клиенте. */
    private function publishBundle(string $version, array $options = []): string
    {
        $zip = $this->makeBundleZip($this->sourceDir."/bundle-{$version}.zip");

        $this->artisan('app:publish-bundle', array_merge([
            'file' => $zip,
            '--bundle-version' => $version,
        ], $options))->assertExitCode(0);

        return $zip;
    }

    /** Минимальный «APK»: ZIP с `AndroidManifest.xml` и подписью v1 (метаданные и хэш). */
    private function makeApk(string $path): string
    {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('AndroidManifest.xml', '<manifest package="com.ledgercraft.app"/>');
        $zip->addFromString('META-INF/CERT.SF', 'Signature-Version: 1.0');
        $zip->close();

        return $path;
    }

    /**
     * Минимальный «бандл веб-слоя»: ZIP, в корне которого лежит `index.html`
     * (именно это проверяет публикация и ждёт плагин OTA).
     */
    private function makeBundleZip(string $path, bool $withIndexHtml = true): string
    {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($withIndexHtml) {
            $zip->addFromString('index.html', '<!doctype html><div id="q-app"></div>');
            $zip->addFromString('assets/index-abc.js', 'console.log("bundle")');
        } else {
            $zip->addFromString('assets/index-abc.js', 'console.log("bundle")');
        }

        $zip->close();

        return $path;
    }

    public function test_download_bundle_returns_the_zip_with_metadata_headers(): void
    {
        $this->publishRelease(12, '1.11');
        $zip = $this->publishBundle('1.11.260915-1210');

        $response = $this->get('/api/download-bundle?version=1.11.260915-1210');

        $response->assertOk();
        $response->assertHeader('X-Bundle-Version', '1.11.260915-1210');
        $this->assertSame(
            (string) hash_file('sha256', $zip),
            $response->headers->get('X-Bundle-Checksum')
        );
        // Отдаётся ровно тот файл, который опубликован (клиент сверяет его хэш).
        $this->assertSame(
            realpath($this->bundlesDir.'/bundle-1.11.260915-1210.zip'),
            realpath((string) $response->getFile()->getPathname())
        );
    }

    public function test_download_without_version_returns_the_latest_bundle(): void
    {
        $this->publishRelease(12, '1.11');
        $this->publishBundle('1.11.260915-1210');

        // Второй бандл публикуем «позже»: порядок задаёт дата публикации.
        $this->travel(5)->seconds();
        $this->publishBundle('1.11.260915-1230');

        $this->getJson('/api/app-version')->assertOk()->assertJsonPath('bundle.version', '1.11.260915-1230');

        $response = $this->get('/api/download-bundle')->assertOk();

        $this->assertSame('1.11.260915-1230', $response->headers->get('X-Bundle-Version'));
    }

    public function test_download_of_unknown_bundle_answers_404(): void
    {
        $this->publishRelease(12, '1.11');
        $this->publishBundle('1.11.260915-1210');

        $this->getJson('/api/download-bundle?version=9.9.999999-9999')
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function test_without_any_bundle_the_download_answers_404(): void
    {
        $this->publishRelease(12, '1.11');

        $this->getJson('/api/download-bundle')->assertStatus(404);
    }

    public function test_republishing_the_same_version_replaces_the_entry(): void
    {
        $this->publishRelease(12, '1.11');
        $this->publishBundle('1.11.260915-1210', ['--notes' => 'первая попытка']);
        $this->publishBundle('1.11.260915-1210', ['--notes' => 'исправленный бандл']);

        $manifest = json_decode((string) File::get($this->bundlesDir.'/bundles.json'), true);

        $this->assertCount(1, $manifest['bundles']);
        $this->assertSame('исправленный бандл', $manifest['bundles'][0]['notes']);
        $this->getJson('/api/app-version')->assertOk()->assertJsonPath('bundle.notes', 'исправленный бандл');
    }

    public function test_zip_without_index_html_is_rejected(): void
    {
        $zip = $this->makeBundleZip($this->sourceDir.'/broken.zip', false);

        $this->artisan('app:publish-bundle', ['file' => $zip, '--bundle-version' => '1.0'])
            ->assertExitCode(1);

        $this->assertFalse(File::exists($this->bundlesDir.'/bundles.json'));
    }

    public function test_non_zip_file_is_rejected(): void
    {
        $path = $this->sourceDir.'/not-a-bundle.zip';
        File::put($path, 'это просто текст, а не архив');

        $this->artisan('app:publish-bundle', ['file' => $path, '--bundle-version' => '1.0'])
            ->assertExitCode(1);

        $this->assertFalse(File::exists($this->bundlesDir.'/bundles.json'));
    }

    public function test_checksum_mismatch_is_rejected(): void
    {
        $zip = $this->makeBundleZip($this->sourceDir.'/bundle.zip');

        $this->artisan('app:publish-bundle', [
            'file' => $zip,
            '--bundle-version' => '1.0',
            '--checksum' => 'совсем-не-тот-хэш',
        ])->assertExitCode(1);

        $this->assertFalse(File::exists($this->bundlesDir.'/bundles.json'));
    }

    /** Скрипт релиза печатает оба хэша, поэтому base64 тоже принимаем (в манифест идёт hex). */
    public function test_checksum_in_base64_is_accepted_too(): void
    {
        $zip = $this->makeBundleZip($this->sourceDir.'/bundle.zip');
        $base64 = base64_encode((string) hash_file('sha256', $zip, true));

        $this->artisan('app:publish-bundle', [
            'file' => $zip,
            '--bundle-version' => '3.0',
            '--checksum' => $base64,
        ])->assertExitCode(0);

        $bundle = app(BundleReleaseRepository::class)->findByVersion('3.0');

        $this->assertSame((string) hash_file('sha256', $zip), $bundle['checksum']);
        $this->assertSame($base64, $bundle['checksumBase64']);
    }

    public function test_repository_exposes_the_manifest_for_the_command(): void
    {
        $repository = app(BundleReleaseRepository::class);
        $zip = $this->makeBundleZip($this->sourceDir.'/bundle.zip');

        $bundle = $repository->publish($zip, ['version' => '1.2.3', 'minNativeVersionCode' => 12]);

        $this->assertSame('1.2.3', $bundle['version']);
        $this->assertSame('bundle-1.2.3.zip', $bundle['fileName']);
        $this->assertSame($repository->checksumHex($zip), $bundle['checksum']);
        $this->assertSame($repository->checksumBase64($zip), $bundle['checksumBase64']);
        $this->assertSame(12, $bundle['minNativeVersionCode']);
        $this->assertNotNull($repository->findByVersion('1.2.3'));
        $this->assertNotNull($repository->filePath($bundle));

        // Публикация одного и того же файла не размножает записи в манифесте.
        $repository->publish($zip, ['version' => '1.2.3']);
        $this->assertCount(1, $repository->all());
    }

    public function test_publish_requires_a_version(): void
    {
        $repository = app(BundleReleaseRepository::class);
        $zip = $this->makeBundleZip($this->sourceDir.'/bundle.zip');

        $this->expectException(RuntimeException::class);
        $repository->publish($zip, ['version' => '  ']);
    }

    /** Репозиторий релизов APK нужен, чтобы в манифесте была версия приложения. */
    public function test_apk_release_repository_is_still_wired(): void
    {
        $this->assertNotNull(app(ApkReleaseRepository::class)->directory());
    }
}
