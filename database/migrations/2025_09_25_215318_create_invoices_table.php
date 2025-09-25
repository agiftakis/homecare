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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            
            // Invoice identification
            $table->string('invoice_number')->unique(); // e.g., "VL-2025-001"
            
            // Invoice period
            $table->date('invoice_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');
            
            // Financial data
            $table->decimal('subtotal', 10, 2); // Total before tax
            $table->decimal('tax_rate', 5, 4)->default(0.0000); // Tax percentage (e.g., 0.0875 = 8.75%)
            $table->decimal('tax_amount', 8, 2)->default(0.00); // Calculated tax amount
            $table->decimal('total_amount', 10, 2); // Final total
            
            // Status tracking
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // PDF storage
            $table->string('pdf_path')->nullable(); // Firebase path to generated PDF
            
            // Client information snapshot (in case client details change)
            $table->string('client_name');
            $table->string('client_email');
            $table->text('client_address')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['agency_id', 'status']);
            $table->index(['client_id', 'invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};