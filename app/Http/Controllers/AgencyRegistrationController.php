<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
// ✅ TIMEZONE FIX: Import the In validation rule to check against a list of valid timezones.
use Illuminate\Validation\Rule;


class AgencyRegistrationController extends Controller
{
    /**
     * Show the agency registration form.
     */
    public function create(Request $request)
    {
        $plan = $request->query('plan', 'basic');
        return view('auth.register-agency', compact('plan'));
    }

    /**
     * Handle the registration of a new agency.
     */
    public function store(Request $request)
    {
        // ✅ START TIMEZONE FIX: Add validation for the new timezone field.
        $validated = $request->validate([
            'agency_name' => 'required|string|max:255|unique:agencies,name',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'plan' => 'required|in:basic,professional,premium,enterprise',
            // Use Laravel's built-in timezone validation rule for robustness.
            'timezone' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
        ]);
        // ✅ END TIMEZONE FIX

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
                    'subscription_plan' => $validated['plan'],
                    'user_id' => $user->id,
                    // ✅ TIMEZONE FIX: Save the validated timezone to the database.
                    'timezone' => $validated['timezone'],
                ]);

                // Now, link the user back to the agency.
                $user->agency_id = $agency->id;
                $user->save();
                
                $agency->createAsStripeCustomer();

                // Login the new user
                Auth::login($user);
                event(new Registered($user));
            });
        } catch (\Exception $e) {
            // If anything goes wrong, we'll undo all database changes.
            return back()->withInput()->with('error', 'There was a critical error during registration. Please try again.');
        }

        // Redirect to the subscription page to collect payment details
        return redirect()->route('subscription.create', ['plan' => $validated['plan']]);
    }
}
