<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Задача 9.3: убрать дубль источника «где лежит товар».
 *
 * `product_stocks.product_categories_id` дублировал `products.product_category_id`:
 * категория товара уже есть у самого товара, а строка остатка — это характеристика
 * товара (одна строка на товар, `Product::stock()` — hasOne). Из-за дубля владельца
 * данных и выборку «остатки категории» приходилось считать по категории в остатке,
 * а не по товару.
 *
 * Что осталось после миграции:
 *   • «где лежит товар» → `products.product_category_id`;
 *   • остаток категории → `products JOIN product_stocks ON product_id`;
 *   • владелец/синк (задача 9.2) — по `product_stocks.product_id` → `products`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            // В PostgreSQL внешний ключ по колонке удаляется вместе с ней.
            $table->dropColumn('product_categories_id');
        });

        // Инвариант: «сколько лежит» — это одна строка на товар, поэтому после правки
        // схемы добиваем строки остатка товарам, у которых их нет (товары, заведённые до
        // 9.2/9.3, и товары, приехавшие синком без остатка). `NOT EXISTS` делает шаг
        // идемпотентным, `quantity = 0` — «ничего не приходовали».
        DB::statement("
            INSERT INTO product_stocks (product_id, quantity, created_at, updated_at)
            SELECT products.id, 0, NOW(), NOW()
            FROM products
            WHERE products.deleted_at IS NULL
              AND NOT EXISTS (
                  SELECT 1 FROM product_stocks WHERE product_stocks.product_id = products.id
              )
        ");
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('product_categories_id')->nullable()->after('id');
            $table->foreign('product_categories_id')
                ->references('id')
                ->on('product_categories')
                ->onDelete('cascade');
        });
    }
};
