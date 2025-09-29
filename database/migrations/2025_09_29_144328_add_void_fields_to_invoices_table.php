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
        Schema::table('invoices', function (Blueprint $table) {
            // Change status enum to include 'void'
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled', 'void'])->default('draft')->change();
            
            // Add void tracking fields
            $table->timestamp('voided_at')->nullable()->after('paid_at');
            $table->bigInteger('voided_by')->unsigned()->nullable()->after('voided_at');
            
            // Add relationship fields to link voided and replacement invoices
            $table->bigInteger('voided_invoice_id')->unsigned()->nullable()->after('voided_by')->comment('The invoice this one replaced (if reissued)');
            $table->bigInteger('replacement_invoice_id')->unsigned()->nullable()->after('voided_invoice_id')->comment('The new invoice created after voiding this one');
            
            // Add foreign key constraints
            $table->foreign('voided_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('voided_invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('replacement_invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['voided_by']);
            $table->dropForeign(['voided_invoice_id']);
            $table->dropForeign(['replacement_invoice_id']);
            
            // Drop columns
            $table->dropColumn(['voided_at', 'voided_by', 'voided_invoice_id', 'replacement_invoice_id']);
            
            // Revert status enum (back to original)
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft')->change();
        });
    }
};