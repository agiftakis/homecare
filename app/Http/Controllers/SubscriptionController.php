<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
// ✅ STEP 1: Import the Mailable and the Email Service.
use App\Mail\WelcomeEmail;
use App\Services\MultiGmailEmailService;

class SubscriptionController extends Controller
{
    /**
     * Show the subscription form.
     */
    public function create(Request $request)
    {
        $plan = $request->query('plan', 'basic');
        $intent = Auth::user()->agency->createSetupIntent();

        return view('subscription.create', compact('plan', 'intent'));
    }

    /**
     * Store the subscription details.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:basic,professional,premium,enterprise',
            'payment_method' => 'required|string',
        ]);

        $user = User::find(Auth::id());
        $agency = $user->agency;

        if (!$agency) {
            Log::error('Subscription failed: Could not find agency for user.', ['user_id' => $user->id]);
            return back()->with('error', 'A problem occurred with your agency account. Please contact support.');
        }

        $planName = $request->plan;
        $paymentMethod = $request->payment_method;

        try {
            // Create the subscription on the agency model
            $agency->newSubscription('default', $this->getStripePriceId($planName))
                ->create($paymentMethod);

            // Update the local subscription_status
            $agency->update(['subscription_status' => 'active']);

            // ✅ STEP 2: Dispatch the welcome email using our service.
            // This will send the email in the background after the payment is successful.
            (new MultiGmailEmailService())->dispatch(new WelcomeEmail($user));

            return redirect()->route('dashboard')->with('success', 'Thank you! Your subscription has been activated.');
        } catch (Exception $e) {
            Log::error('Stripe Subscription Failed: ' . $e->getMessage(), ['user_id' => $user->id]);
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Show the subscription management page with current subscription details
     * and generate a secure URL to Stripe's Customer Portal for billing management.
     */
    public function manage()
    {
        $user = Auth::user();
        $agency = $user->agency;

        if (!$agency) {
            return redirect()->route('dashboard')->with('error', 'Agency not found.');
        }

        // Get the current subscription
        $subscription = $agency->subscription('default');
        
        // Get subscription details
        $subscriptionData = [
            'plan' => $this->getPlanNameFromSubscription($subscription),
            'status' => $subscription ? $subscription->stripe_status : 'inactive',
            'current_period_end' => $subscription ? $subscription->asStripeSubscription()->current_period_end : null,
            'cancel_at_period_end' => $subscription ? $subscription->asStripeSubscription()->cancel_at_period_end : false,
        ];

        // Generate Stripe Customer Portal URL
        $portalUrl = null;
        if ($agency->hasStripeId()) {
            try {
                $portalUrl = $agency->billingPortalUrl(route('dashboard'));
            } catch (Exception $e) {
                Log::error('Failed to generate Stripe portal URL: ' . $e->getMessage(), ['agency_id' => $agency->id]);
            }
        }

        return view('subscription.manage', compact('subscriptionData', 'portalUrl'));
    }

    /**
     * A helper function to get the correct Stripe Price ID from the .env file.
     */
    private function getStripePriceId($planName)
    {
        return match ($planName) {
            'professional' => env('STRIPE_PROFESSIONAL_PRICE_ID'),
            'premium' => env('STRIPE_PREMIUM_PRICE_ID'),
            'enterprise' => env('STRIPE_ENTERPRISE_PRICE_ID'),
            default => env('STRIPE_BASIC_PRICE_ID'),
        };
    }

    /**
     * ✅ NEW: Helper function to get the plan name from the subscription
     */
    private function getPlanNameFromSubscription($subscription)
    {
        if (!$subscription) {
            return 'No Active Plan';
        }

        $stripePriceId = $subscription->stripe_price;
        
        return match ($stripePriceId) {
            env('STRIPE_PROFESSIONAL_PRICE_ID') => 'Professional',
            env('STRIPE_PREMIUM_PRICE_ID') => 'Premium', 
            env('STRIPE_ENTERPRISE_PRICE_ID') => 'Enterprise',
            env('STRIPE_BASIC_PRICE_ID') => 'Basic',
            default => 'Unknown Plan',
        };
    }
}