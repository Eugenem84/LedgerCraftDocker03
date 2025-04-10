<?php

namespace App\Repositories;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

}
