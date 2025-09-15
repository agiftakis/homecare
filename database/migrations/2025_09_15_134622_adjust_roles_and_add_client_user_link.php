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
        // Fix #1: Change the 'role' column to a flexible string to allow 'client' and other future roles.
        // This will also resolve the ENUM issue.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->change(); // Using a varchar(20) is plenty
        });

        // Fix #2: Add the user_id column to the clients table to create the link.
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('agency_id')->constrained()->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reversing this is tricky without knowing the original ENUM values.
            // For development, this is a safe reversal.
            $table->string('role', 20)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};