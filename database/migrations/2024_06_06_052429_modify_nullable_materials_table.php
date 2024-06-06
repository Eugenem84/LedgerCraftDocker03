<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->unsignedBigInteger('price')->nullable()->change();
            $table->smallInteger('amount')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->smallInteger('amount')->nullable(false)->change();
        });
    }
};
