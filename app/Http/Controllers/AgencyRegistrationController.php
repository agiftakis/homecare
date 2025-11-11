<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rule;
// Use DateTimeZone for timezone logic
use DateTimeZone;

class AgencyRegistrationController extends Controller
{
    /**
     * Show the agency registration form.
     */
    public function create(Request $request)
    {
        // ✅ REMOVED: No longer need plan parameter since Stripe is gone
        // $plan = $request->query('plan', 'basic');

        // ✅ REQUIREMENT: Filter timezones for North America only.
        $allTimezones = DateTimeZone::listIdentifiers();
        $northAmericaTimezones = array_filter($allTimezones, function ($timezone) {
            return strpos($timezone, 'America/') === 0;
        });

        // Pass the filtered list to the view.
        return view('auth.register-agency', compact('northAmericaTimezones'));
    }

    /**
     * Handle the registration of a new agency.
     */
    public function store(Request $request)
    {
        // ✅ REQUIREMENT: Generate the valid North American timezone list for validation.
        $allTimezones = DateTimeZone::listIdentifiers();
        $northAmericaTimezones = array_filter($allTimezones, function ($timezone) {
            return strpos($timezone, 'America/') === 0;
        });

        $validated = $request->validate([
            'agency_name' => 'required|string|max:255|unique:agencies,name',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            // ✅ REMOVED: No longer validating 'plan' since Stripe is removed
            // 'plan' => 'required|in:basic,professional,premium,enterprise',
            // ✅ REQUIREMENT: Validate that the selected timezone is in our North America list.
            'timezone' => ['required', 'string', Rule::in($northAmericaTimezones)],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Create the User FIRST
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'agency_admin',
                ]);

                // Create the Agency and immediately link it to the owner (user).
                $agency = Agency::create([
                    'name' => $validated['agency_name'],
                    'contact_email' => $validated['email'],
                    // ✅ REMOVED: No longer saving subscription_plan (field doesn't exist)
                    // 'subscription_plan' => $validated['plan'],
                    'user_id' => $user->id,
                    'timezone' => $validated['timezone'],
                    'is_lifetime_free' => false, // ✅ NEW: Default to false, super admin can change later
                ]);

                // Now, link the user back to the agency.
                $user->agency_id = $agency->id;
                $user->save();

                // ✅ REMOVED: No longer creating Stripe customer
                // $agency->createAsStripeCustomer();

                // Login the new user
                Auth::login($user);
                event(new Registered($user));
            });
        } catch (\Exception $e) {
            // If anything goes wrong, we'll undo all database changes.
            return back()->withInput()->with('error', 'There was a critical error during registration. Please try again.');
        }

        // ✅ CHANGED: Redirect to dashboard instead of subscription page
        return redirect()->route('dashboard')->with('success', 'Your agency has been registered successfully! A super admin will review and activate your account.');
    }
}