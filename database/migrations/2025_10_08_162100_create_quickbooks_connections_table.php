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
        Schema::create('quickbooks_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');
            $table->string('realm_id'); // QuickBooks company ID
            $table->text('access_token'); // OAuth access token
            $table->text('refresh_token'); // OAuth refresh token
            $table->timestamp('token_expires_at')->nullable(); // When access token expires
            $table->timestamp('refresh_token_expires_at')->nullable(); // When refresh token expires
            $table->boolean('is_active')->default(true); // Connection status
            $table->timestamp('last_synced_at')->nullable(); // Last successful sync
            $table->timestamps();

            // One connection per agency
            $table->unique('agency_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quickbooks_connections');
    }
};
