<?php

namespace Tests\Feature;

use App\Repositories\ApkReleaseRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

/**
 * Версия приложения и раздача APK — Фаза 13, задачи 13.1/13.2.
 *
 * Критерий: `/api/app-version` отдаёт `versionCode`, `apkUrl` и `sha256`, а
 * скачанный файл совпадает по хэшу. Заодно фиксируем дефект, из-за которого
 * контроллер брал `glob()[0]` (порядок файловой системы) вместо самого свежего
 * релиза и не отдавал `versionCode` вовсе.
 *
 * Тесты не ходят в БД (релизы — это файлы), поэтому `RefreshDatabase` не нужен:
 * каталоги подменяются через `config('app-versions.*')`.
 */
class AppVersionTest extends TestCase
{
    private string $baseDir;

    private string $releasesDir;

    private string $legacyDir;

    private string $sourceDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->baseDir = storage_path('framework/testing/app-versions-'.uniqid());
        $this->releasesDir = $this->baseDir.'/releases';
        $this->legacyDir = $this->baseDir.'/legacy';
        $this->sourceDir = $this->baseDir.'/sources';

        foreach ([$this->releasesDir, $this->legacyDir, $this->sourceDir] as $dir) {
            File::ensureDirectoryExists($dir);
        }

        Config::set('app-versions.directory', $this->releasesDir);
        Config::set('app-versions.legacy_directory', $this->legacyDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->baseDir);

        parent::tearDown();
    }

    public function test_without_any_release_the_endpoint_answers_404(): void
    {
        $this->getJson('/api/app-version')
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function test_publishing_a_release_is_visible_in_the_endpoint(): void
    {
        $apk = $this->makeApk($this->sourceDir.'/app-release.apk');

        $this->artisan('app:publish-apk', [
            'file' => $apk,
            '--version-code' => 2,
            '--version-name' => '1.1',
            '--notes' => 'Чиним склад',
        ])->assertExitCode(0);

        $published = $this->releasesDir.'/2-1.1.apk';

        $this->assertFileExists($published);
        $this->assertFileExists($this->releasesDir.'/releases.json');

        $response = $this->getJson('/api/app-version')->assertOk();

        $response->assertJson([
            'versionCode' => 2,
            'versionName' => '1.1',
            'notes' => 'Чиним склад',
            'mandatory' => false,
            'minSupportedVersionCode' => 0,
            // Совместимость со старым ответом контроллера.
            'version' => '1.1',
            'apk_name' => '2-1.1.apk',
        ]);

        // Хэш и размер — из фактического файла: клиент сверяет их перед установкой.
        $response->assertJson([
            'sha256' => hash_file('sha256', $published),
            'sizeBytes' => filesize($published),
        ]);

        $this->assertStringContainsString('/api/download-apk', (string) $response->json('apkUrl'));
        $this->assertStringContainsString('versionCode=2', (string) $response->json('apkUrl'));
    }

    public function test_the_biggest_version_code_wins_not_the_file_timestamp(): void
    {
        $this->publishRelease(2, '1.1');
        $this->publishRelease(3, '1.2');

        // Старый APK «свежее» по времени: прежняя логика (`glob()[0]` + `filemtime`)
        // вернула бы именно его, а скачивание отдало бы другой файл.
        touch($this->releasesDir.'/2-1.1.apk', time() + 60);

        $this->getJson('/api/app-version')
            ->assertOk()
            ->assertJson(['versionCode' => 3, 'versionName' => '1.2']);

        // Старые релизы не удаляем: на них ещё могут сидеть устройства.
        $this->assertFileExists($this->releasesDir.'/2-1.1.apk');
    }

    public function test_mandatory_and_min_supported_version_are_exposed(): void
    {
        $apk = $this->makeApk($this->sourceDir.'/forced.apk');

        $this->artisan('app:publish-apk', [
            'file' => $apk,
            '--version-code' => 4,
            '--version-name' => '2.0',
            '--min-version' => 3,
            '--mandatory' => true,
        ])->assertExitCode(0);

        $this->getJson('/api/app-version')
            ->assertOk()
            ->assertJson(['mandatory' => true, 'minSupportedVersionCode' => 3]);
    }

    public function test_repeated_publication_of_the_same_version_code_replaces_the_entry(): void
    {
        $this->publishRelease(2, '1.1', ['--notes' => 'первая попытка']);
        $this->publishRelease(2, '1.1', ['--notes' => 'пересобрали']);

        $releases = app(ApkReleaseRepository::class)->all();

        $this->assertCount(1, $releases);
        $this->assertSame('пересобрали', $releases[0]['notes']);
    }

    public function test_download_serves_the_requested_release_with_metadata_headers(): void
    {
        $this->publishRelease(2, '1.1');
        $this->publishRelease(3, '1.2');

        $response = $this->get('/api/download-apk?versionCode=2');

        $response->assertOk();
        $response->assertHeader('X-Apk-Version-Code', '2');
        $response->assertHeader('X-Apk-Version-Name', '1.1');
        $response->assertHeader('X-Apk-Sha256', (string) hash_file('sha256', $this->releasesDir.'/2-1.1.apk'));

        $this->assertStringContainsString(
            'application/vnd.android.package-archive',
            (string) $response->headers->get('content-type')
        );

        // Скачанный файл — тот самый релиз: клиент сверяет хэш перед установкой.
        $served = (string) file_get_contents($response->getFile()->getPathname());

        $this->assertSame(hash_file('sha256', $this->releasesDir.'/2-1.1.apk'), hash('sha256', $served));
    }

    public function test_download_without_parameter_serves_the_latest_release(): void
    {
        $this->publishRelease(2, '1.1');
        $this->publishRelease(3, '1.2');

        $this->get('/api/download-apk')
            ->assertOk()
            ->assertHeader('X-Apk-Version-Code', '3');
    }

    public function test_download_of_unknown_version_code_answers_404(): void
    {
        $this->publishRelease(2, '1.1');

        $this->getJson('/api/download-apk?versionCode=99')
            ->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function test_text_file_is_rejected_and_manifest_is_not_created(): void
    {
        $fake = $this->sourceDir.'/not-an-apk.txt';
        File::put($fake, 'это не apk');

        $this->artisan('app:publish-apk', ['file' => $fake, '--version-code' => 2, '--version-name' => '1.1'])
            ->assertExitCode(1);

        $this->assertFileDoesNotExist($this->releasesDir.'/releases.json');
    }

    public function test_zip_without_android_manifest_is_rejected(): void
    {
        $apk = $this->makeApk($this->sourceDir.'/no-manifest.apk', withManifest: false);

        $this->artisan('app:publish-apk', ['file' => $apk, '--version-code' => 2])
            ->assertExitCode(1);

        $this->assertFileDoesNotExist($this->releasesDir.'/releases.json');
    }

    public function test_publishing_without_version_code_fails_with_explanation(): void
    {
        $apk = $this->makeApk($this->sourceDir.'/no-code.apk');

        // `aapt` на сервере обычно нет — versionCode нужно передать явно.
        $this->artisan('app:publish-apk', ['file' => $apk])
            ->expectsOutputToContain('versionCode')
            ->assertExitCode(1);
    }

    public function test_legacy_apk_without_manifest_is_still_served(): void
    {
        File::copy($this->makeApk($this->sourceDir.'/legacy.apk'), $this->legacyDir.'/LedgerCraft-0.9.3.apk');

        $this->getJson('/api/app-version')
            ->assertOk()
            ->assertJson(['versionCode' => null, 'versionName' => '0.9.3', 'legacy' => true]);

        $this->get('/api/download-apk')->assertOk();
    }

    /** Публикация релиза через artisan — как это делает скрипт релиза (задача 13.4). */
    private function publishRelease(int $versionCode, string $versionName, array $options = []): void
    {
        $apk = $this->makeApk($this->sourceDir."/app-{$versionCode}-{$versionName}.apk");

        $this->artisan('app:publish-apk', array_merge([
            'file' => $apk,
            '--version-code' => $versionCode,
            '--version-name' => $versionName,
        ], $options))->assertExitCode(0);
    }

    /**
     * Минимальный «APK» для тестов: ZIP с `AndroidManifest.xml` и/или подписью v1.
     * Настоящий APK не нужен — контроллер смотрит только метаданные и хэш.
     */
    private function makeApk(string $path, bool $withManifest = true, bool $withSignature = true): string
    {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($withManifest) {
            $zip->addFromString('AndroidManifest.xml', '<manifest package="com.ledgercraft.app"/>');
        }

        if ($withSignature) {
            $zip->addFromString('META-INF/CERT.SF', 'Signature-Version: 1.0');
        }

        $zip->addFromString('classes.dex', 'fixture');
        $zip->close();

        return $path;
    }
}
