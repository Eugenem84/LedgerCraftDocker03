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
                sum(orders.total_amount)
                    FILTER ( WHERE date_trunc('day', created_at) = date_trunc('day', current_date) AND status = 'done')
                    as total_day,
                sum(orders.total_amount)
                    FILTER ( WHERE date_trunc('week', created_at) = date_trunc('week', current_date) AND status = 'done')
                    as total_week,
                sum(orders.total_amount)
                    FILTER ( WHERE date_trunc('month', created_at) = date_trunc('month', current_date) AND status = 'done')
                    as total_month,
                sum(orders.total_amount) FILTER ( WHERE date_trunc('year', created_at) = date_trunc('year', current_date) AND status = 'done')
                    as total_year
            FROM orders
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
}
