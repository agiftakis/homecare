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
        Schema::create('quickbooks_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who triggered sync
            $table->string('entity_type'); // 'invoice', 'customer', 'payment'
            $table->unsignedBigInteger('entity_id'); // VitaLink ID
            $table->string('quickbooks_id')->nullable(); // QuickBooks ID
            $table->string('action'); // 'create', 'update', 'delete'
            $table->string('status'); // 'pending', 'success', 'failed'
            $table->text('error_message')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['agency_id', 'entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quickbooks_sync_logs');
    }
};