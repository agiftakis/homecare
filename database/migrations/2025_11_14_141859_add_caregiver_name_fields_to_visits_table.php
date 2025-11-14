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
        Schema::table('visits', function (Blueprint $table) {
            // Add caregiver name fields to preserve caregiver info even after deletion
            $table->string('caregiver_first_name')->nullable()->after('agency_id');
            $table->string('caregiver_last_name')->nullable()->after('caregiver_first_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['caregiver_first_name', 'caregiver_last_name']);
        });
    }
};