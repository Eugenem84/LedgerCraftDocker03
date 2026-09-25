<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="referrer" content="no-referrer">
    <title>{{ $title }} — Ledger Craft</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #0f1115;
            color: #e7eaf0;
            font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #171a21;
            border: 1px solid #262b35;
            border-radius: 16px;
            padding: 28px 22px;
            text-align: center;
        }
        .mark {
            width: 54px;
            height: 54px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: #f5c542;
            color: #171a21;
            font-weight: 800;
            font-size: 20px;
            letter-spacing: .04em;
        }
        h1 { margin: 0 0 8px; font-size: 20px; font-weight: 600; }
        p { margin: 0 0 20px; color: #9aa3b2; font-size: 15px; }
        .btn {
            display: block;
            width: 100%;
            padding: 14px 16px;
            border-radius: 10px;
            background: #f5c542;
            color: #171a21;
            font-weight: 600;
            text-decoration: none;
        }
        .hint { margin: 18px 0 0; font-size: 13px; color: #7c8697; }
        .hint a { color: #f5c542; }
    </style>
</head>
<body>
<div class="card">
    <div class="mark">LC</div>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>

    {{-- Ссылка на схему приложения: письма используют https (его не вырезают
         мейл-клиенты), а сама схема нужна здесь как фолбэк, если Android App Links
         домен ещё не верифицировал. --}}
    <a class="btn" href="{{ $deepLink }}">Открыть приложение</a>

    <p class="hint">
        Приложение не открылось? Установите его со
        <a href="{{ url('/promo/') }}">страницы загрузки</a>.
    </p>
</div>

<script>
    // Пробуем открыть приложение сразу: при установленном приложении браузер
    // уйдёт в него и страница останется позади. Если не получилось — пользователь
    // видит кнопку выше.
    window.location.replace(@json($deepLink));
</script>
</body>
</html>
