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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_email'); // We added this in a later migration
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('subscription_plan', ['basic', 'professional', 'premium', 'enterprise']);
            $table->enum('subscription_status', ['active', 'cancelled', 'expired', 'trial']);
            
            // **THE FIX:** The 'trial_ends_at' column is now handled by the Laravel Cashier migration,
            // so we remove it from here to prevent a conflict.
            // $table->timestamp('trial_ends_at')->nullable(); 

            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
