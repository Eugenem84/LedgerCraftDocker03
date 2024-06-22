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
        ", [
            'specialization_id' => $specializationId
        ]);
    }
}
