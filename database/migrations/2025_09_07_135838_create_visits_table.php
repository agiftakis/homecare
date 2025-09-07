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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            // Foreign key to link this visit to a specific shift
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');

            // Foreign key for multi-tenancy, linking to the agency
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');

            // Timestamps for the actual visit
            $table->timestamp('clock_in_time');
            $table->timestamp('clock_out_time')->nullable();

            // Path to the signature image file in Firebase Storage
            $table->string('signature_path');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};