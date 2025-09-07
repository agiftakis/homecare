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
            // Add a new column to store the path for the clock-out signature.
            // It's nullable because it will be empty until the caregiver clocks out.
            // We'll place it right after the original signature_path for organization.
            $table->string('clock_out_signature_path')->nullable()->after('signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // This ensures we can safely roll back the migration if needed.
            $table->dropColumn('clock_out_signature_path');
        });
    }
};