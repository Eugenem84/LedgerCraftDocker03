<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Единый тип денег на сервере (задача 3.12, продолжение 2.3): всё в ЦЕЛЫХ рублях.
 *
 * `services.price` исторически был VARCHAR, поэтому сервер кастовал его прямо в SQL
 * (`CAST(services.price AS numeric)`, `price::INTEGER`), а сравнение и сортировка шли
 * как по тексту. Остальные денежные колонки уже integer/bigint (проверено по схеме),
 * так что приводим только эту.
 *
 * Нечисловые значения (если успели попасть в БД) и пустые строки → 0: колонка NOT NULL,
 * а «цена не задана» для услуги исторически означала пустое значение.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!$this->isTextColumn('services', 'price')) {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE services ALTER COLUMN price TYPE integer USING (
                COALESCE(
                    CASE
                        WHEN btrim(price) ~ '^-?[0-9]+([.,][0-9]+)?$'
                            THEN round(replace(btrim(price), ',', '.')::numeric)::integer
                    END,
                    0
                )
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE services ALTER COLUMN price TYPE VARCHAR(255)');
    }

    private function isTextColumn(string $table, string $column): bool
    {
        $row = DB::selectOne(
            "select data_type from information_schema.columns
             where table_schema = 'public' and table_name = ? and column_name = ?",
            [$table, $column]
        );

        return $row && in_array($row->data_type, ['character varying', 'character', 'text'], true);
    }
};
