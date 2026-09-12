<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Аналитика мастерской.
 *
 * Задача 9.1 («свести методику выручки к одной»). До правки «выручка» считалась
 * тремя разными формулами — `SUM(services.price)`, `SUM(quantity * sale_price)` и
 * `SUM(orders.total_amount)` (причём последняя — без фильтров `paid`/`status`),
 * поэтому цифры на одном экране не сходились. Теперь методика одна и живёт в
 * одном месте — `billedOrdersSubquery()`:
 *
 *   • учитываются только «учтённые» заказы: `status = 'done'`, `paid = true` и не
 *     удалённые (`deleted_at IS NULL`) — выручка это деньги, полученные за работу;
 *   • выручка заказа = его позиции: работы (`order_service.quantity * sale_price`)
 *     + товары (`order_product.quantity * sale_price`)
 *     + ручные материалы (`materials.amount * price`);
 *   • период — по `orders.updated_at` (дата закрытия/последнего изменения заказа):
 *     так работали исходные запросы, и это единственная «дата продажи» у заказа;
 *   • цена позиции (`sale_price`) важнее каталожной `services.price`: в заказе
 *     могла быть скидка;
 *   • число заказов считается по заказам (а не по строкам позиций), поэтому заказ
 *     без позиций входит в средний чек как нулевая сумма.
 *
 * Клиент (`src/utils/analytics.js` + `analyticsRepo.js`) считает локально по той же
 * методике (офлайн-первый), поэтому цифры страницы аналитики совпадают с отчётами
 * сервера. Тесты: `tests/Feature/StatisticRepositoryTest.php` (сервер) и
 * `test/analytics.test.js` / `test/analytics-repo.test.js` (клиент).
 */
class StatisticRepository
{
    /** Сколько позиций отдаём в топах. */
    private const TOP_LIMIT = 10;

    /**
     * «Учтённые» заказы мастерской: одна строка на заказ, с готовой выручкой.
     *
     * Суммы позиций собираются подзапросами, а не `JOIN`-ами подряд: иначе строки
     * работ, товаров и материалов перемножились бы между собой и выручка «раздулась».
     *
     * `$specializationId` подставляется в SQL числом: это целое из роута, а сам
     * подзапрос нужен и в `DB::select`, и в конструкторе запросов (там именованный
     * биндинг из подзапроса не подхватился бы).
     */
    private function billedOrdersSubquery($specializationId): string
    {
        $specializationId = (int) $specializationId;

        return "
            SELECT
                orders.id,
                orders.client_id,
                orders.updated_at,
                COALESCE(services_lines.total, 0)
                    + COALESCE(products_lines.total, 0)
                    + COALESCE(materials_lines.total, 0) AS revenue,
                COALESCE(products_lines.cost, 0)
                    + COALESCE(materials_lines.cost, 0) AS cost
            FROM orders
            LEFT JOIN (
                SELECT order_id, SUM(quantity * sale_price) AS total
                FROM order_service
                GROUP BY order_id
            ) AS services_lines ON services_lines.order_id = orders.id
            LEFT JOIN (
                SELECT
                    order_id,
                    SUM(quantity * sale_price) AS total,
                    SUM(quantity * COALESCE(buy_price, 0)) AS cost
                FROM order_product
                GROUP BY order_id
            ) AS products_lines ON products_lines.order_id = orders.id
            LEFT JOIN (
                SELECT
                    order_id,
                    SUM(amount * price) AS total,
                    SUM(amount * COALESCE(buy_price, 0)) AS cost
                FROM materials
                GROUP BY order_id
            ) AS materials_lines ON materials_lines.order_id = orders.id
            WHERE orders.specialization_id = {$specializationId}
              AND orders.status = 'done'
              AND orders.paid = true
              AND orders.deleted_at IS NULL
        ";
    }

    /**
     * Выручка и маржа за день / неделю / месяц / год (как в web-отчёте «доход за …»).
     * Неделя — с понедельника (`date_trunc('week')`), границы — серверное «сегодня».
     *
     * Маржа = выручка − себестоимость позиций (задачи 9.5/9.6): закупка товаров со склада
     * и ручных позиций. У работ себестоимости нет — это труд мастера.
     */
    public function getProfitDWMY($specializationId)
    {
        return DB::select("
            SELECT
                COALESCE(SUM(revenue) FILTER (WHERE date_trunc('day', updated_at) = date_trunc('day', current_date)), 0)::bigint   AS total_day,
                COALESCE(SUM(revenue) FILTER (WHERE date_trunc('week', updated_at) = date_trunc('week', current_date)), 0)::bigint  AS total_week,
                COALESCE(SUM(revenue) FILTER (WHERE date_trunc('month', updated_at) = date_trunc('month', current_date)), 0)::bigint AS total_month,
                COALESCE(SUM(revenue) FILTER (WHERE date_trunc('year', updated_at) = date_trunc('year', current_date)), 0)::bigint  AS total_year,
                COALESCE(SUM(revenue - cost) FILTER (WHERE date_trunc('day', updated_at) = date_trunc('day', current_date)), 0)::bigint   AS margin_day,
                COALESCE(SUM(revenue - cost) FILTER (WHERE date_trunc('week', updated_at) = date_trunc('week', current_date)), 0)::bigint  AS margin_week,
                COALESCE(SUM(revenue - cost) FILTER (WHERE date_trunc('month', updated_at) = date_trunc('month', current_date)), 0)::bigint AS margin_month,
                COALESCE(SUM(revenue - cost) FILTER (WHERE date_trunc('year', updated_at) = date_trunc('year', current_date)), 0)::bigint  AS margin_year
            FROM ({$this->billedOrdersSubquery($specializationId)}) AS billed
        ");
    }

    /**
     * Топ работ: сколько раз выполнили (по количеству в позициях) и сколько заработали.
     * Считается по цене позиции (`sale_price`), а не по каталожной `services.price`.
     */
    public function getTopServicesBySpecialization($specializationId)
    {
        return DB::select("
            SELECT
                services.service                                              AS service,
                COALESCE(SUM(order_service.quantity), 0)::bigint               AS service_count,
                services.price                                                AS price,
                COALESCE(SUM(order_service.quantity * order_service.sale_price), 0)::bigint AS total
            FROM order_service
            JOIN services ON services.id = order_service.service_id
            JOIN ({$this->billedOrdersSubquery($specializationId)}) AS billed
                ON billed.id = order_service.order_id
            GROUP BY order_service.service_id, services.service, services.price
            ORDER BY total DESC, service_count DESC
            LIMIT " . self::TOP_LIMIT . "
        ");
    }

    /**
     * Топ клиентов по выручке. Раньше суммировался `orders.total_amount` вообще без
     * фильтров — теперь та же методика, что и везде, поэтому суммы сходятся с DWMY.
     */
    public function getTopProfitClients($specializationId)
    {
        return DB::select("
            SELECT
                clients.name                                              AS name,
                COUNT(billed.id)::int                                      AS num_ord,
                COALESCE(SUM(billed.revenue), 0)::bigint                    AS total_amount,
                COALESCE(SUM(billed.revenue - billed.cost), 0)::bigint      AS margin
            FROM ({$this->billedOrdersSubquery($specializationId)}) AS billed
            JOIN clients ON clients.id = billed.client_id
            GROUP BY clients.id, clients.name
            ORDER BY total_amount DESC
            LIMIT " . self::TOP_LIMIT . "
        ");
    }

    /**
     * Топ товаров со склада в заказах. Товар мог быть удалён из справочника — строка
     * позиции всё равно учитывается (в имени будет заглушка), иначе выручка «терялась» бы.
     *
     * `margin` — маржа (задачи 9.5/9.6): цена минус закупка на момент продажи. Если у строки
     * закупка не заполнена, она считается нулевой — это видно как «маржа = выручка».
     */
    public function getTopProductsBySpecialization($specializationId)
    {
        return DB::select("
            SELECT
                COALESCE(products.name, '—')                                  AS name,
                COALESCE(SUM(order_product.quantity), 0)::bigint               AS product_count,
                COALESCE(SUM(order_product.quantity * order_product.sale_price), 0)::bigint AS total,
                COALESCE(SUM(order_product.quantity
                    * (order_product.sale_price - COALESCE(order_product.buy_price, 0))), 0)::bigint AS margin
            FROM order_product
            JOIN ({$this->billedOrdersSubquery($specializationId)}) AS billed
                ON billed.id = order_product.order_id
            LEFT JOIN products ON products.id = order_product.product_id
            GROUP BY order_product.product_id, products.name
            ORDER BY total DESC, product_count DESC
            LIMIT " . self::TOP_LIMIT . "
        ");
    }

    /**
     * Топ ручных позиций («купил на стороне»): таблица `materials` хранит имя строкой,
     * поэтому группируем по имени. `margin` — маржа по позициям (задачи 9.5/9.6).
     */
    public function getTopMaterialsBySpecialization($specializationId)
    {
        return DB::select("
            SELECT
                materials.name                                               AS name,
                COALESCE(SUM(materials.amount), 0)::bigint                    AS material_count,
                COALESCE(SUM(materials.amount * materials.price), 0)::bigint   AS total,
                COALESCE(SUM(materials.amount
                    * (materials.price - COALESCE(materials.buy_price, 0))), 0)::bigint AS margin
            FROM materials
            JOIN ({$this->billedOrdersSubquery($specializationId)}) AS billed
                ON billed.id = materials.order_id
            GROUP BY materials.name
            ORDER BY total DESC, material_count DESC
            LIMIT " . self::TOP_LIMIT . "
        ");
    }

    /**
     * Распределение заказов по текущему статусу (сколько «в ожидании»/«в работе»/«готово»).
     * Здесь периода нет намеренно: это «что сейчас в работе», а не выручка за отрезок.
     * Удалённые заказы не считаем.
     */
    public function getStatusDistribution($specializationId)
    {
        return DB::select("
            SELECT
                COALESCE(status, 'unknown') AS status,
                COUNT(*)::int               AS count
            FROM orders
            WHERE specialization_id = ?
              AND deleted_at IS NULL
            GROUP BY status
            ORDER BY count DESC
        ", [(int) $specializationId]);
    }

    /** Выручка текущего года по месяцам (для web-отчёта). */
    public function getIncomeByYear($specializationId)
    {
        return DB::table(DB::raw("({$this->billedOrdersSubquery($specializationId)}) AS billed"))
            ->selectRaw("date_part('month', billed.updated_at)     AS month_number")
            ->selectRaw("to_char(billed.updated_at, 'Mon')        AS month_name")
            ->selectRaw('COALESCE(SUM(billed.revenue), 0)::bigint AS total')
            ->whereYear('billed.updated_at', Carbon::now()->year)
            ->groupByRaw("date_part('month', billed.updated_at), to_char(billed.updated_at, 'Mon')")
            ->orderByRaw("date_part('month', billed.updated_at)")
            ->get();
    }

    /**
     * Выручка с группировкой по периоду. Ветки `day`/`week`/`month` в контроллере уходят
     * в отдельные методы ниже, сюда попадают остальные (`year` и неизвестные значения).
     */
    public function getIncomeByTimePeriod($specializationId, $period)
    {
        $query = DB::table(DB::raw("({$this->billedOrdersSubquery($specializationId)}) AS billed"))
            ->selectRaw('COALESCE(SUM(billed.revenue), 0)::bigint AS total')
            ->whereBetween('billed.updated_at', $this->getDateRange($period));

        switch ($period) {
            case 'month':
                $query->selectRaw("to_char(billed.updated_at, 'Mon') AS period")
                    ->groupByRaw("date_part('month', billed.updated_at), to_char(billed.updated_at, 'Mon')")
                    ->orderByRaw("date_part('month', billed.updated_at)");
                break;
            case 'week':
                $query->selectRaw("to_char(billed.updated_at, 'IYYY-\"W\"IW') AS period")
                    ->groupByRaw("to_char(billed.updated_at, 'IYYY-\"W\"IW')")
                    ->orderByRaw("to_char(billed.updated_at, 'IYYY-\"W\"IW')");
                break;
            case 'day':
                $query->selectRaw("to_char(billed.updated_at, 'YYYY-MM-DD') AS period")
                    ->groupByRaw("to_char(billed.updated_at, 'YYYY-MM-DD')")
                    ->orderByRaw("to_char(billed.updated_at, 'YYYY-MM-DD')");
                break;
            case 'year':
            default:
                $query->selectRaw('EXTRACT(year FROM billed.updated_at) AS period')
                    ->groupByRaw('EXTRACT(year FROM billed.updated_at)')
                    ->orderByRaw('EXTRACT(year FROM billed.updated_at)');
                break;
        }

        return $query->get();
    }


    /** Выручка по дням за последние 30 дней: пустые дни отдаются нулями (для графика). */
    public function getIncomeByDay($specializationId)
    {
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $aggregateData = DB::table(DB::raw("({$this->billedOrdersSubquery($specializationId)}) AS billed"))
            ->selectRaw('DATE(billed.updated_at) AS date')
            ->selectRaw('COALESCE(SUM(billed.revenue), 0) AS total')
            ->whereBetween('billed.updated_at', [$startDate, $endDate])
            ->groupByRaw('DATE(billed.updated_at)')
            ->pluck('total', 'date')
            ->map(fn ($item) => (float) $item);

        $allDates = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $allDates[$currentDate->format('Y-m-d')] = 0;
            $currentDate->addDay();
        }

        return collect(array_replace($allDates, $aggregateData->toArray()))
            ->sortKeys()
            ->map(fn ($total, $date) => ['period' => $date, 'total' => $total])
            ->values();
    }

    /** Выручка по неделям за последние 15 недель (пустые — нулями). */
    public function getIncomeByWeek($specializationId)
    {
        $startDate = Carbon::now()->subWeeks(15)->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();
        $weekFormat = "IYYY-\"W\"IW";

        $aggregateData = DB::table(DB::raw("({$this->billedOrdersSubquery($specializationId)}) AS billed"))
            ->selectRaw("TO_CHAR(billed.updated_at, '{$weekFormat}') AS week")
            ->selectRaw('COALESCE(SUM(billed.revenue), 0) AS total')
            ->whereBetween('billed.updated_at', [$startDate, $endDate])
            ->groupByRaw("TO_CHAR(billed.updated_at, '{$weekFormat}')")
            ->pluck('total', 'week')
            ->map(fn ($item) => (float) $item);

        $allWeeks = [];
        $currentWeek = $startDate->copy();

        while ($currentWeek <= $endDate) {
            $allWeeks[$currentWeek->isoFormat('YYYY-[W]WW')] = 0;
            $currentWeek->addWeek();
        }

        return collect(array_replace($allWeeks, $aggregateData->toArray()))
            ->sortKeys()
            ->map(fn ($total, $week) => ['period' => $week, 'total' => $total])
            ->values();
    }


    /** Выручка по месяцам за последние 12 месяцев (пустые — нулями). */
    public function getIncomeByMonth($specializationId)
    {
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $aggregateData = DB::table(DB::raw("({$this->billedOrdersSubquery($specializationId)}) AS billed"))
            ->selectRaw("TO_CHAR(billed.updated_at, 'YYYY-MM') AS month")
            ->selectRaw('COALESCE(SUM(billed.revenue), 0) AS total')
            ->whereBetween('billed.updated_at', [$startDate, $endDate])
            ->groupByRaw("TO_CHAR(billed.updated_at, 'YYYY-MM')")
            ->pluck('total', 'month')
            ->map(fn ($item) => (float) $item);

        $allMonths = [];
        $currentMonth = $startDate->copy();

        while ($currentMonth <= $endDate) {
            $allMonths[$currentMonth->format('Y-m')] = 0;
            $currentMonth->addMonth();
        }

        return collect(array_replace($allMonths, $aggregateData->toArray()))
            ->sortKeys()
            ->map(fn ($total, $month) => ['period' => $month, 'total' => $total])
            ->values();
    }

    /**
     * Выручка, себестоимость, маржа, число заказов и средний чек по периодам выбранного
     * масштаба (день — 30 дней, неделя — 15 недель, месяц — 12 месяцев, год — 5 лет).
     */
    public function getStatsByPeriod(int $specializationId, string $period)
    {
        $groupFormat = match ($period) {
            'day'   => 'YYYY-MM-DD',
            'week'  => 'IYYY-"W"IW',
            'month' => 'YYYY-MM',
            'year'  => 'YYYY',
            default => 'YYYY-MM-DD',
        };

        return DB::table(DB::raw("({$this->billedOrdersSubquery($specializationId)}) AS billed"))
            ->selectRaw("to_char(billed.updated_at, '{$groupFormat}') AS period")
            ->selectRaw('COALESCE(SUM(billed.revenue), 0) AS total')
            ->selectRaw('COALESCE(SUM(billed.cost), 0)    AS cost')
            ->selectRaw('COALESCE(SUM(billed.revenue - billed.cost), 0) AS margin')
            ->selectRaw('COUNT(*)                        AS count')
            ->whereBetween('billed.updated_at', $this->getDateRange($period))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => [
                'period' => $row->period,
                'total'  => (float) $row->total,
                'cost'   => (float) $row->cost,
                'margin' => (float) $row->margin,
                // Наценка к себестоимости: у периода без закупок её нет (`null`), а не 0 %.
                'margin_percent' => $row->cost > 0
                    ? (int) round(($row->margin / $row->cost) * 100)
                    : null,
                'count'  => (int) $row->count,
                // Средний чек: заказы без позиций входят как нулевые — выручка делится
                // на число учтённых заказов, а не только на «заказы с позициями».
                'avg'    => $row->count > 0 ? (int) round($row->total / $row->count) : 0,
            ])->values();
    }

    /** Диапазоны для `getStatsByPeriod` — те же, что у клиентской страницы аналитики. */
    protected function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'day'   => [$now->copy()->subDays(30)->startOfDay(), $now->copy()->endOfDay()],
            'week'  => [$now->copy()->subWeeks(15)->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->subMonths(11)->startOfMonth(), $now->copy()->endOfMonth()],
            'year'  => [$now->copy()->subYears(4)->startOfYear(), $now->copy()->endOfDay()],
            default => [$now->copy()->subDays(30)->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}

