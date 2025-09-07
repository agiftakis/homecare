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
            // This will store the secure, one-time use token.
            // It's 'unique' to ensure no two users can have the same token.
            $table->string('password_setup_token')->nullable()->unique()->after('remember_token');

            // This will store the timestamp for when the token expires.
            $table->timestamp('password_setup_expires_at')->nullable()->after('password_setup_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['password_setup_token', 'password_setup_expires_at']);
        });
    }
};