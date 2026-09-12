<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Пресеты специализаций на сервере (Фаза 10, задача 10.7, решение D5).
 *
 * Сервер — источник пресетов: поправить контент можно без релиза приложения,
 * а клиент держит read-only кэш и офлайн работает из него (плюс фолбэк на
 * клиентские JSON из 10.4).
 *
 * `content` — JSON: категории работ → услуги с ценами, категории товаров, модели.
 * `version` — версия контента (клиент сравнивает и «дотягивает» новое).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialization_templates', function (Blueprint $table) {
            $table->id();
            $table->string('preset_key')->unique();
            $table->unsignedInteger('version')->default(1);
            $table->json('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialization_templates');
    }
};
