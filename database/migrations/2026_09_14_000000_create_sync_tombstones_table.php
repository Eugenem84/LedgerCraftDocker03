<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tombstones для таблиц синка без колонки `deleted_at` (задача 3.9).
 *
 * Удаление должно «доезжать» до других устройств, но у части синкаемых таблиц
 * (`specializations`, `materials`, `order_product`, `product_categories`,
 * `product_stocks`, `incoming_products`, `buy_product_prices`,
 * `sales_products_prices`) нет soft-delete: строка удаляется физически, и второе
 * устройство о ней никогда не узнаёт. Поэтому факт удаления складываем сюда, а
 * `/sync-updates` отдаёт его как запись с флагом `deleted` (tombstone).
 *
 * Таблицы, у которых `deleted_at` есть (`clients`, `products`, `services`,
 * `categories`, `equipment_models`, `orders`, `order_service`), в tombstone
 * не нуждаются — там tombstone'ом служит сама soft-deleted строка.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sync_tombstones')) {
            return;
        }

        Schema::create('sync_tombstones', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->index();
            $table->bigInteger('record_id')->nullable();
            $table->string('uuid_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
            // Владелец (задача 3.10): tombstone отдаём только его устройствам.
            $table->unsignedBigInteger('user_id')->nullable()->index();
            // Анти-эхо (задача 3.6): устройство-автор не получает своё же удаление.
            $table->string('last_sync_id')->nullable()->index();
            $table->timestamps();

            // Повторное удаление (идемпотентность, задача 3.5) не плодит tombstone'ы.
            $table->unique(['table_name', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_tombstones');
    }
};
