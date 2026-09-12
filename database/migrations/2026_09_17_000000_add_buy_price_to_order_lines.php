<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `buy_price` в позициях заказа — себестоимость на момент продажи (задачи 9.5/9.6, D2).
 *
 * До этой миграции «прибыль» в отчётах была равна выручке: закупка (`buy_product_prices`,
 * `incoming_products.by_price`) хранилась отдельно от заказа и в расчётах не участвовала.
 * Теперь себестоимость лежит в самой позиции:
 *   • `order_product.buy_price` — товар со склада (закупка на момент продажи);
 *   • `materials.buy_price` — ручная позиция («купил по пути»).
 *
 * `order_service` не трогаем: у работы себестоимости нет (это труд мастера), её маржа
 * равна цене позиции — так же считает `StatisticRepository`.
 *
 * Колонки nullable: `NULL` = «закупка неизвестна» (маржа по строке просто не считается,
 * а не «вся выручка — прибыль»).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->unsignedInteger('buy_price')->nullable()->after('quantity');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->unsignedInteger('buy_price')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->dropColumn('buy_price');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('buy_price');
        });
    }
};
