<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    // `http://localhost:9000` / `:9001` — локальный dev-сервер Quasar (`npm run dev`).
    // `https://localhost` / `http://localhost` / `capacitor://localhost` — мобильный клиент
    // Capacitor: WebView отдаёт origin `https://localhost` (`androidScheme` по умолчанию
    // `https`, см. CapConfig.java), iOS — `capacitor://localhost`.
    // ⚠️ Без origin приложения Laravel отвечает на preflight `204` **без**
    // `Access-Control-Allow-Origin`, WebView блокирует запрос ещё до Laravel, и клиент
    // показывает «Нет связи с сервером» — хотя интернет на телефоне есть.
    'allowed_origins' => [
        'http://localhost:9000',
        'http://localhost:9001',
        'https://localhost',
        'http://localhost',
        'capacitor://localhost',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
