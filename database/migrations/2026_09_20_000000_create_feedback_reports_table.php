<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Отчёты «Сообщить об ошибке» (Фаза 14, задачи 14.4/14.6, решение D7).
 *
 * Отчёт — **не** строка синхронизации: он приходит отдельной ручкой `POST /api/feedback`,
 * не участвует в `$tables`/`sync-updates` и не уезжает на другие устройства владельца.
 *
 * `uuid_id` — клиентский id отчёта: по нему отчёт ищется при повторе (идемпотентность,
 * тот же приём, что в синке, задача 3.5). `payload` — отчёт целиком в том виде, в каком
 * его собрал клиент (контракт `docs/FEEDBACK.md` §3): колонки выше нужны для выборок и
 * фильтров, а `payload` — источник правды для выгрузки в инбокс (`feedback/INBOX.md`).
 * `status` — рабочий статус разбора (`new`/`read`/`accepted`/`rejected`), его ставит
 * разработчик/агент, а не клиент.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_reports', function (Blueprint $table) {
            $table->id();
            // Клиентский id отчёта — строка, а не `uuid`: колонка должна принимать
            // любой id, который сгенерировал клиент (идемпотентность важнее формата).
            $table->string('uuid_id', 64)->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('kind', 20)->default('bug');
            $table->text('message');
            $table->string('contact', 200)->nullable();
            $table->string('screen', 200)->nullable();
            $table->string('app_version', 100)->nullable();
            $table->string('platform', 32)->nullable();
            $table->string('platform_version', 200)->nullable();
            $table->string('device', 100)->nullable();
            $table->string('api_url', 200)->nullable();
            $table->unsignedInteger('schema_version')->nullable();
            $table->unsignedInteger('schema_stored')->nullable();
            $table->string('account_email', 200)->nullable();
            $table->string('profile_name', 200)->nullable();
            $table->jsonb('payload');
            $table->jsonb('sync')->nullable();
            $table->jsonb('errors')->nullable();
            $table->jsonb('logs')->nullable();
            $table->timestamp('client_created_at')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->text('resolution_note')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_reports');
    }
};
