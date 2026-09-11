<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Анти-эхо синка (задача 3.6, серверная половина).
 *
 * `last_sync_id` — метка устройства (`X-Sync-ID`), которое последним изменило запись.
 * `SyncController::fetchUpdates()` отдаёт устройству только записи с чужим
 * `last_sync_id` (или пустым): своё же изменение устройство уже применило локально,
 * и получать его обратно ему не нужно.
 *
 * Заполняет колонку сам `SyncController` при insert/update/soft-delete — код был
 * написан заранее и «включается» через `Schema::hasColumn`. Индекс — под фильтр
 * выдачи (`last_sync_id != :sync_id OR last_sync_id IS NULL`).
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
        'order_service',
        'products',
        'product_categories',
        'product_stocks',
        'categories',
        'services',
        'buy_product_prices',
        'sales_products_prices',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'last_sync_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('last_sync_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'last_sync_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['last_sync_id']);
                $blueprint->dropColumn('last_sync_id');
            });
        }
    }
};
