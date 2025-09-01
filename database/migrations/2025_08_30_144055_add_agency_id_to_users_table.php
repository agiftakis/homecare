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
        Schema::table('users', function (Blueprint $table) {
            // This allows the Super Admin to exist without an agency
            $table->foreignId('agency_id')->nullable()->constrained()->onDelete('cascade'); 
            
            // This defines the different user roles in our system
            $table->enum('role', ['super_admin', 'agency_admin', 'staff', 'caregiver'])->default('staff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // This properly reverses the changes made in the 'up' method
            $table->dropForeign(['agency_id']);
            $table->dropColumn(['agency_id', 'role']);
        });
    }
};

