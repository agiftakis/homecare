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
        Schema::table('clients', function (Blueprint $table) {
            // ADD CODE HERE - after the care_plan column
            $table->text('current_medications')->nullable()->after('care_plan');
            $table->text('discontinued_medications')->nullable()->after('current_medications');
            $table->text('recent_hospitalizations')->nullable()->after('discontinued_medications');
            $table->text('current_concurrent_dx')->nullable()->after('recent_hospitalizations');
            $table->string('designated_poa')->nullable()->after('current_concurrent_dx');
            $table->text('current_routines_am_pm')->nullable()->after('designated_poa');
            $table->enum('fall_risk', ['yes', 'no'])->nullable()->after('current_routines_am_pm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // ADD CODE HERE
            $table->dropColumn([
                'current_medications',
                'discontinued_medications',
                'recent_hospitalizations',
                'current_concurrent_dx',
                'designated_poa',
                'current_routines_am_pm',
                'fall_risk'
            ]);
        });
    }
};
