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
            // Add the user_id column and create the foreign key link to the users table.
            // If a user account is deleted, the user_id on the caregiver will be set to NULL
            // without deleting the caregiver's professional record.
            $table->foreignId('user_id')->nullable()->after('agency_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caregivers', function (Blueprint $table) {
            // This allows the migration to be undone safely.
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

