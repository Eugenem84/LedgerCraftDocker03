<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Идемпотентность синка (задача 3.5).
 *
 * Каждая синкаемая таблица получает колонку `uuid_id` — это клиентский
 * идентификатор записи (`local_id` из payload операции). По нему
 * `SyncController` делает «найти или вставить»: повторная отправка того же
 * батча (например, после обрыва связи между ответом и разбором ответа на
 * клиенте) не создаёт дублей.
 *
 * Уникальный индекс — гарантия на уровне БД: гонка двух параллельных
 * запросов не породит две строки с одним `uuid_id` (второй insert упадёт и
 * будет откатан SAVEPOINT'ом, после чего повторится как update).
 *
 * У `order_service` колонка `uuid_id` уже есть (миграция 2026_04_05),
 * поэтому она пропускается — идемпотентность этой таблицы обеспечивается
 * натуральным ключом `order_id + service_id` (своего PK у неё нет).
 */
return new class extends Migration
{
    /**
     * Таблицы синка — совпадают с `SyncController::$tables`.
     *
     * @var array<int, string>
     */
    private array $tables = [
        'clients',
        'specializations',
        'orders',
        'equipment_models',
        'incoming_products',
        'materials',
        'order_product',
        'product_stocks',
        'products',
        'product_categories',
        'categories',
        'services',
        'buy_product_prices',
        'sales_products_prices',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'uuid_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->uuid('uuid_id')->nullable();
                $blueprint->unique('uuid_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'uuid_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropUnique(['uuid_id']);
                $blueprint->dropColumn('uuid_id');
            });
        }
    }
};
