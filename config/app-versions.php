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

];
