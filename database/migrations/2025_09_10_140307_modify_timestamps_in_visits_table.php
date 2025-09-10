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
        // This migration provides the definitive fix for the timestamp corruption bug.
        // It alters the columns to DATETIME to prevent MySQL's automatic
        // ON UPDATE CURRENT_TIMESTAMP behavior associated with the TIMESTAMP type.
        Schema::table('visits', function (Blueprint $table) {
            $table->dateTime('clock_in_time')->change();
            $table->dateTime('clock_out_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting may not be perfect, but this is the logical reversal.
        Schema::table('visits', function (Blueprint $table) {
            $table->timestamp('clock_in_time')->change();
            $table->timestamp('clock_out_time')->nullable()->change();
        });
    }
};
