<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Заказ может быть **без клиента**.
 *
 * Офлайн-сценарий мастерской: заказ заводят «на приёмке» (приняли велосипед/авто),
 * а клиента вписывают позже — в клиенте поле `client_id` необязательное
 * (`useOrderDraftStore`: `client_id: this.client?.id ?? null`), и заказы без клиента
 * уже лежат в локальных БД устройств.
 *
 * Колонка была `unsignedBigInteger` NOT NULL, поэтому синк отвечал `DATABASE_ERROR`
 * (`null value in column "client_id" violates not-null constraint`) и такой заказ
 * навсегда оставался в очереди устройства — операция повторялась при каждом sync.
 *
 * Внешний ключ на `clients` сохраняем (NULL допустим, FK отрабатывает при удалении
 * клиента). `->change()` не используем: в Laravel 10 он требует doctrine/dbal,
 * которого в проекте нет — пишем явный SQL (проект работает на PostgreSQL).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->assertPostgres();

        DB::statement('ALTER TABLE orders ALTER COLUMN client_id DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->assertPostgres();

        // Вернуть NOT NULL можно только когда заказов без клиента нет: иначе
        // PostgreSQL откажется навешивать ограничение.
        $orphans = DB::table('orders')->whereNull('client_id')->count();

        if ($orphans > 0) {
            throw new RuntimeException(
                "Нельзя вернуть NOT NULL для orders.client_id: заказов без клиента — {$orphans}. ".
                'Сначала привяжите их к клиентам (или оставьте колонку nullable).'
            );
        }

        DB::statement('ALTER TABLE orders ALTER COLUMN client_id SET NOT NULL');
    }

    /**
     * Миграция написана на «сыром» SQL под PostgreSQL (см. docblock класса).
     */
    private function assertPostgres(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'pgsql') {
            throw new RuntimeException(
                "Миграция orders.client_id рассчитана на PostgreSQL, текущий драйвер: {$driver}."
            );
        }
    }
};
