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
        Schema::table('caregivers', function (Blueprint $table) {
            // ADD CODE HERE - after the certifications column
            $table->string('certifications_filename')->nullable()->after('certifications');
            $table->string('certifications_path')->nullable()->after('certifications_filename');
            $table->string('professional_licenses_filename')->nullable()->after('certifications_path');
            $table->string('professional_licenses_path')->nullable()->after('professional_licenses_filename');
            $table->string('state_province_id_filename')->nullable()->after('professional_licenses_path');
            $table->string('state_province_id_path')->nullable()->after('state_province_id_filename');
        });
    }

    public function down(): void
    {
        Schema::table('caregivers', function (Blueprint $table) {
            // ADD CODE HERE
            $table->dropColumn([
                'certifications_filename',
                'certifications_path',
                'professional_licenses_filename',
                'professional_licenses_path',
                'state_province_id_filename',
                'state_province_id_path'
            ]);
        });
    }
};
