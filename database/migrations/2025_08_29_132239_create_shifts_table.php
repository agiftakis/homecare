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
    Schema::create('shifts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained()->onDelete('cascade');
        $table->foreignId('caregiver_id')->nullable()->constrained()->onDelete('set null');
        
        // Scheduled Times
        $table->dateTime('start_time');
        $table->dateTime('end_time');

        // Actual Clock-in/Out Times (recorded by caregiver)
        $table->dateTime('clock_in_time')->nullable();
        $table->dateTime('clock_out_time')->nullable();

        // Status Tracking
        $table->string('status')->default('scheduled'); // scheduled, open, in_progress, completed, cancelled

        // Visit Verification Signatures
        $table->text('clock_in_signature')->nullable();
        $table->text('clock_out_signature')->nullable();

        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
