<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use App\Models\Agency;
use App\Models\Subscription;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierController
{
    /**
     * Handle a Stripe webhook call for a customer subscription update.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleCustomerSubscriptionUpdated(array $payload)
    {
        try {
            $stripeSubscription = $payload['data']['object'];
            $stripeCustomerId = $stripeSubscription['customer'];
            $stripePriceId = $stripeSubscription['items']['data'][0]['price']['id'];
            $stripeStatus = $stripeSubscription['status'];

            // Find the agency using the Stripe customer ID
            $agency = Agency::where('stripe_id', $stripeCustomerId)->first();

            if ($agency) {
                $subscription = $agency->subscription('default');

                if ($subscription) {
                    // Update the local subscription record
                    $subscription->stripe_price = $stripePriceId;
                    $subscription->stripe_status = $stripeStatus;
                    $subscription->save();

                    Log::info('Webhook: Successfully updated subscription for agency.', ['agency_id' => $agency->id, 'new_stripe_price' => $stripePriceId]);
                    return new Response('Webhook Handled', 200);
                } else {
                    Log::warning('Webhook: Received subscription update, but no local subscription found.', ['stripe_customer_id' => $stripeCustomerId]);
                }
            } else {
                Log::warning('Webhook: Received subscription update, but no agency found for customer.', ['stripe_customer_id' => $stripeCustomerId]);
            }
        } catch (\Exception $e) {
            Log::error('Webhook Error: Failed to handle customer.subscription.updated.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return new Response('Webhook Error', 500);
        }
        
        return new Response('Webhook Handled but no action taken', 200);
    }
}