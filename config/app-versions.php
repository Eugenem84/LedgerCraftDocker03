<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Релизы Android-приложения (Фаза 13)
    |--------------------------------------------------------------------------
    |
    | Клиент самообновления читает манифест `releases.json` и качает APK из этого
    | каталога. Пишет манифест команда `php artisan app:publish-apk`
    | (см. `App\Repositories\ApkReleaseRepository`).
    |
    | Каталог вынесен в config, чтобы тесты подменяли его через `config()` и не
    | трогали реальный storage (см. `tests/Feature/AppVersionTest.php`).
    |
    */

    'directory' => env('APP_VERSIONS_DIR', storage_path('app/public/releases')),

    /*
    | Куда клали APK до Фазы 13 (`storage/app/public/*.apk`). Читается только как
    | запасной путь: пока манифеста нет, контроллер отдаёт самый свежий файл отсюда.
    */
    'legacy_directory' => env('APP_VERSIONS_LEGACY_DIR', storage_path('app/public')),

    /*
    |--------------------------------------------------------------------------
    | OTA-бандлы веб-слоя (Фаза 15)
    |--------------------------------------------------------------------------
    |
    | Обновление без установки APK: клиент скачивает zip с веб-сборкой, сверяет
    | sha256 (в base64) и применяет его при следующем запуске. Здесь лежат сами zip
    | и манифест `bundles.json` — его пишет `php artisan app:publish-bundle`
    | (`App\Repositories\BundleReleaseRepository`), а `/api/app-version` отдаёт
    | последний бандл полем `bundle` (см. `docs/API-INTEGRATION.md` §2.5).
    |
    | Каталог в config — чтобы тесты подменяли его через `config()` и не трогали
    | реальный storage (см. `tests/Feature/BundleReleaseTest.php`).
    |
    */
    'bundles_directory' => env('APP_BUNDLES_DIR', storage_path('app/public/bundles')),

];
