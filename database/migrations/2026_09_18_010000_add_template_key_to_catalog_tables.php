<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Пометка «пришло из пресета» в каталоге (Фаза 10, задача 10.4, решение D5).
 *
 * Применение пресета материализует обычные записи мастерской; `template_key`
 * (например `bike:wheels`) отмечает происхождение и служит ключом идемпотентности:
 * повторное применение не плодит дубли. Колонки — только у родителей пресета
 * (услуги/товары создаются как дети уже найденной категории).
 */
return new class extends Migration
{
    private array $tables = ['categories', 'product_categories', 'equipment_models'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('template_key')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['template_key']);
                $blueprint->dropColumn('template_key');
            });
        }
    }
};
