<?php

namespace App\Repositories;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

class StatisticRepository
{
    public function getProfitDWMY($specializationId)
    {
        return DB::select("
            SELECT
                sum(CAST(services.price as numeric))
                    FILTER ( WHERE date_trunc('day', orders.updated_at) = date_trunc('day', current_date)
                                AND status = 'done'
                                AND paid = true)
                    as total_day,
                sum(CAST(services.price as numeric))
                    FILTER ( WHERE date_trunc('week', orders.updated_at) = date_trunc('week', current_date)
                                AND status = 'done'
                                AND paid = true)
                    as total_week,
                sum(CAST(services.price as numeric))
                    FILTER ( WHERE date_trunc('month', orders.updated_at) = date_trunc('month', current_date)
                                AND status = 'done'
                                AND paid = true)
                    as total_month,
                sum(CAST(services.price as numeric)) FILTER ( WHERE date_trunc('year', orders.updated_at) = date_trunc('year', current_date)
                                AND status = 'done'
                                AND paid = true)
                    as total_year
            FROM orders
            JOIN order_service ON orders.id = order_service.order_id
            JOIN services ON order_service.service_id = services.id
            WHERE
                specialization_id = :specialization_id
        ", ['specialization_id' => $specializationId]);
    }

    public function getTopServicesBySpecialization($specializationId)
    {
        return DB::select("
            select
                services.service,
                count(*) as service_count,
                services.price,
                count(*) * cast(services.price as numeric ) as total
            from order_service
            join services on order_service.service_id = services.id
            where order_id in (select orders.id
                   from orders
                   where specialization_id = :specialization_id)
            group by order_service.service_id, services.price, services.service
            order by service_count desc
            limit 10
        ", ['specialization_id' => $specializationId]);
    }

    public function getTopProfitClients($specializationId)
    {
        return DB::select("
            SELECT clients.name,
            COUNT(orders.id) as num_ord,
            SUM(orders.total_amount) as total_amount
            FROM clients JOIN orders ON clients.id = orders.client_id
            WHERE orders.specialization_id = :specialization_id
            GROUP BY clients.name
            ORDER BY total_amount DESC
            LIMIT 10
        ", ['specialization_id' => $specializationId]);
    }

    public function getIncomeByYear($specializationId)
    {
        return DB::table('orders')
            ->selectRaw("
            date_part('month', updated_at) AS month_number,
            to_char(updated_at, 'Mon')      AS month_name,
            SUM(total_amount)               AS total
        ")
            ->where('specialization_id', $specializationId)
            ->where('status', 'done')
            ->where('paid', true)
            ->whereYear('updated_at', Carbon::now()->year) // текущий год
            ->groupByRaw("date_part('month', updated_at), to_char(updated_at, 'Mon')")
            ->orderByRaw("date_part('month', updated_at)")
            ->get();
    }

    public function getIncomeByTimePeriod($specializationId, $period)
    {
        $query = DB::table('orders')
            ->select(DB::raw('SUM(total_amount) as total'));

        // Условия фильтрации
        $query->where('specialization_id', $specializationId);
        $query->where('status', 'done');
        $query->where('paid', 1);

        // Динамическая группировка и сортировка в зависимости от выбранного периода
        switch ($period) {
            case 'month':
                $query->whereYear('updated_at', now()->year);
                $query->selectRaw('to_char(updated_at, \'Mon\') as period');
                $query->groupBy(DB::raw('date_part(\'month\', updated_at), to_char(updated_at, \'Mon\')'));
                $query->orderBy(DB::raw('date_part(\'month\', updated_at)'));
                break;
            case 'week':
                $query->whereYear('updated_at', now()->year);
                $query->selectRaw('EXTRACT(week FROM updated_at) as period');
                $query->groupBy(DB::raw('EXTRACT(week FROM updated_at)'));
                $query->orderBy(DB::raw('EXTRACT(week FROM updated_at)'));
                break;
            case 'day':
                $query->whereYear('updated_at', now()->year);
                $query->whereMonth('updated_at',now()->month);
                $query->selectRaw('to_char(updated_at, \'YYYY-MM-DD\') as period');
                $query->groupBy(DB::raw('to_char(updated_at, \'YYYY-MM-DD\')'));
                $query->orderBy(DB::raw('to_char(updated_at, \'YYYY-MM-DD\')'));
                break;
            case 'year':
                $query->selectRaw('EXTRACT(year FROM updated_at) as period');
                $query->groupBy(DB::raw('EXTRACT(year FROM updated_at)'));
                $query->orderBy(DB::raw('EXTRACT(year FROM updated_at)'));
                break;
        }

        // Получаем данные
        return $query->get();
    }

    public function getIncomeByDay($specializationId)
    {
        // Устанавливаем диапазон: последние 7 дней
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(30)->startOfDay(); // 30 дней включая сегодня

        // Запрос к БД
        $aggregateData = DB::table('orders')
            ->join('order_service', 'orders.id', '=', 'order_service.order_id')
            ->select(
                DB::raw("DATE(orders.updated_at) as date"),
                DB::raw('SUM(order_service.quantity * order_service.sale_price) as total')
            )
            ->where('orders.specialization_id', $specializationId)
            ->whereBetween('orders.updated_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(orders.updated_at)'))
            ->pluck('total', 'date')
            ->map(function ($item) {
                return (float)$item;
            });

        // Генерируем все даты за последние 7 дней
        $allDates = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $allDates[$dateString] = 0;
            $currentDate->addDay();
        }

        // Объединяем данные
        $mergedData = array_merge($allDates, $aggregateData->toArray());

        // Сортируем по дате и форматируем
        return collect($mergedData)
            ->sortKeys() // Сортировка по дате
            ->map(function ($total, $date) {
                return [
                    'period' => $date,
                    'total' => $total
                ];
            })
            ->values();
    }

    public function getIncomeByWeek($specializationId)
    {
        $endDate = Carbon::now()->endOfWeek();
        $startDate = Carbon::now()->subWeeks(15)->startOfWeek();

        // Используем одинарные кавычки и экранирование для формата
        $weekFormat = "IYYY-\"W\"IW";

        $aggregateData = DB::table('orders')
            ->join('order_service', 'orders.id', '=', 'order_service.order_id')
            ->select(
                DB::raw("TO_CHAR(orders.updated_at, '".$weekFormat."') AS week"),
                DB::raw('SUM(order_service.quantity * order_service.sale_price) as total')
            )
            ->where('orders.specialization_id', $specializationId)
            ->whereBetween('orders.updated_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            ->groupBy(DB::raw("TO_CHAR(orders.updated_at, '".$weekFormat."')"))
            ->pluck('total', 'week')
            ->map(function ($item) {
                return (float)$item;
            });

        // Генерация недель с ISO-форматом
        $allWeeks = [];
        $currentWeek = $startDate->copy();

        while ($currentWeek <= $endDate) {
            $weekKey = $currentWeek->isoFormat('YYYY-[W]WW');
            $allWeeks[$weekKey] = 0;
            $currentWeek->addWeek();
        }

        $mergedData = array_replace($allWeeks, $aggregateData->toArray());

        return collect($mergedData)
            ->sortKeys()
            ->map(function ($total, $week) {
                return [
                    'period' => $week,
                    'total' => $total
                ];
            })
            ->values();
    }
}
