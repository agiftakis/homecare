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
        Schema::create('visit_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->onDelete('cascade');
            $table->foreignId('modified_by')->constrained('users')->onDelete('cascade');
            $table->string('action'); // 'created', 'clock_out', 'note_updated', 'note_deleted'
            $table->json('changes')->nullable(); // Store what changed
            $table->text('reason')->nullable(); // Optional reason for the change
            $table->timestamp('modified_at');
            $table->timestamps();

            // Indexes for performance
            $table->index('visit_id');
            $table->index('modified_by');
            $table->index('modified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_modifications');
    }
};