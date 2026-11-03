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
        Schema::table('orders_details', function (Blueprint $table) {
            $table->boolean('is_checkin')->default(false);
            $table->datetime('checkin_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_details', function (Blueprint $table) {
            $table->dropColumn('is_checkin');
            $table->dropColumn('checkin_time');
        });
    }
};
