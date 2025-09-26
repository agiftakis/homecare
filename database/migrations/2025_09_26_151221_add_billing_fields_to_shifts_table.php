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
        Schema::table('shifts', function (Blueprint $table) {
            // Add billing fields to shifts table
            $table->decimal('hourly_rate', 8, 2)->default(25.00)->after('status'); // Default $25/hour
            $table->string('service_type')->default('personal_care')->after('hourly_rate'); // Type of care service
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['hourly_rate', 'service_type']);
        });
    }
};