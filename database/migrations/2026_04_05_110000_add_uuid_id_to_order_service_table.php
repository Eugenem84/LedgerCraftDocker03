<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_service', function (Blueprint $table) {
            $table->uuid('uuid_id')->nullable()->after('id');
            $table->uuid('uuid_order_id')->nullable()->after('uuid_id');
            $table->uuid('uuid_service_id')->nullable()->after('uuid_order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_service', function (Blueprint $table) {
            $table->dropColumn('uuid_id');
            $table->dropColumn('uuid_order_id');
            $table->dropColumn('uuid_service_id');
        });
    }
};
