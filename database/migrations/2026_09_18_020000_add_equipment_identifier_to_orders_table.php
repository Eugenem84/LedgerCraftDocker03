<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Универсальный идентификатор объекта заказа (Фаза 10, задача 10.9, решение D5).
 *
 * Разным нишам нужен один и тот же по смыслу «внешний идентификатор объекта»:
 * автосервису — VIN/госномер, кондиционерам — адрес объекта, велосервису —
 * серийник рамы. Схему заказа по нишам НЕ ветвим — одно опциональное поле,
 * подпись берётся из лексикона профиля (задача 10.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('equipment_identifier')->nullable()->after('model_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('equipment_identifier');
        });
    }
};
