<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Show the subscription creation page.
     */
    public function create(Request $request)
    {
        $plan = $request->query('plan', 'basic');
        $intent = Auth::user()->agency->createSetupIntent();
        return view('subscription.create', compact('plan', 'intent'));
    }

    /**
     * Store the new subscription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:basic,professional,premium,enterprise',
            'payment_method' => 'required|string',
        ]);

        $agency = Auth::user()->agency;
        $planName = $request->plan;
        $paymentMethod = $request->payment_method;

        try {
            // We have removed the ->trialDays(14) line.
            // This will now charge the user's card immediately.
            $agency->newSubscription('default', $this->getStripePriceId($planName))
                ->create($paymentMethod);

            // The success message is updated to reflect an immediate charge.
            return redirect()->route('dashboard')->with('success', 'Thank you! Your subscription has been activated.');
        } catch (Exception $e) {
            // THIS IS THE CRITICAL CHANGE: We log the detailed error.
            Log::error('Stripe Subscription Failed: ' . $e->getMessage());

            // We will also make sure the error message gets back to the user.
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
    /**
     * A helper function to get the correct Stripe Price ID from the .env file.
     */
    private function getStripePriceId($plan)
    {
        return match (strtolower($plan)) {
            'professional' => env('STRIPE_PROFESSIONAL_PRICE_ID'),
            'premium' => env('STRIPE_PREMIUM_PRICE_ID'),
            'enterprise' => env('STRIPE_ENTERPRISE_PRICE_ID'),
            'basic' => env('STRIPE_BASIC_PRICE_ID'),
            default => env('STRIPE_BASIC_PRICE_ID'),
        };
    }
}
