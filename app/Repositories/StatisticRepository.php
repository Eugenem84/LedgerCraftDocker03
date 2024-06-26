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
}
