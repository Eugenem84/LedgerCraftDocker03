<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement('
          UPDATE order_service
            SET sale_price = (
                SELECT price::INTEGER FROM services
                WHERE services.id = order_service.service_id
    )
    WHERE EXISTS (
        SELECT 1 FROM services
        WHERE services.id = order_service.service_id
    )
');
    }

    public function down()
    {
        DB::statement('UPDATE order_service SET sale_price = NULL');
    }
};
