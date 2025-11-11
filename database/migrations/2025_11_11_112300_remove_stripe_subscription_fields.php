<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the tables created by Cashier
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        // Remove the Stripe-related columns from the 'agencies' table
        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                
                // Drop Stripe-specific columns
                if (Schema::hasColumn('agencies', 'subscription_plan')) {
                    $table->dropColumn('subscription_plan');
                }
                if (Schema::hasColumn('agencies', 'subscription_status')) {
                    $table->dropColumn('subscription_status');
                }
                if (Schema::hasColumn('agencies', 'subscription_ends_at')) {
                    $table->dropColumn('subscription_ends_at');
                }
                if (Schema::hasColumn('agencies', 'stripe_id')) {
                    
                    // ✅ *** THE REAL FIX IS HERE ***
                    // I have removed all the complex, failing code.
                    // This one line tells Laravel to find the index
                    // associated with the 'stripe_id' column and drop it.
                    // This requires 'doctrine/dbal', which you have installed.
                    $table->dropIndex(['stripe_id']);
                    
                    // Now we can safely drop the column itself.
                    $table->dropColumn('stripe_id');
                }
                if (Schema::hasColumn('agencies', 'pm_type')) {
                    $table->dropColumn('pm_type');
                }
                if (Schema::hasColumn('agencies', 'pm_last_four')) {
                    $table->dropColumn('pm_last_four');
                }
                if (Schema::hasColumn('agencies', 'trial_ends_at')) {
                    $table->dropColumn('trial_ends_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Re-create the tables (optional, but good practice for rollbacks)
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->index(['agency_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->integer('quantity')->nullable();
            $table->timestamps();
            $table->index(['subscription_id', 'stripe_price']);
        });

        // Re-add the columns to the 'agencies' table
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('subscription_plan')->nullable()->after('timezone');
            $table->string('subscription_status')->nullable()->after('subscription_plan');
            $table->timestamp('subscription_ends_at')->nullable()->after('is_lifetime_free');
            $table->string('stripe_id')->nullable()->index()->after('subscription_ends_at');
            $table->string('pm_type')->nullable()->after('stripe_id');
            $table->string('pm_last_four', 4)->nullable()->after('pm_type');
            $table->timestamp('trial_ends_at')->nullable()->after('pm_last_four');
        });
    }
};