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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('visit_id')->constrained()->onDelete('cascade'); // Links to actual visit record
            
            // Service details
            $table->string('service_description'); // e.g., "Personal Care - 09/25/2025"
            $table->string('service_type'); // Snapshot from shift
            $table->date('service_date');
            
            // Time and billing details
            $table->time('start_time'); // From visit clock_in_time
            $table->time('end_time'); // From visit clock_out_time
            $table->decimal('hours_worked', 5, 2); // Calculated hours (e.g., 4.25)
            $table->decimal('hourly_rate', 6, 2); // Rate at time of service
            $table->decimal('line_total', 8, 2); // hours_worked * hourly_rate
            
            // Caregiver information (snapshot)
            $table->string('caregiver_name'); // Extracted from signature paths or caregiver record
            
            $table->timestamps();
            
            // Index for performance
            $table->index(['invoice_id', 'service_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};