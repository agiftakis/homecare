<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

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
        $validated = $request->validate([
            'agency_name' => 'required|string|max:255|unique:agencies,name',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'plan' => 'required|in:basic,professional,premium,enterprise'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // **FIX 1: Create the User FIRST**
                // This gives us a user_id to link to the agency.
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'agency_admin',
                ]);

                // **FIX 2: Create the Agency and immediately link it to the owner (user).**
                $agency = Agency::create([
                    'name' => $validated['agency_name'],
                    'contact_email' => $validated['email'],
                    'subscription_plan' => $validated['plan'],
                    'user_id' => $user->id, // This is the crucial link for the "Owner" column.
                ]);

                // Now, link the user back to the agency.
                $user->agency_id = $agency->id;
                $user->save();
                
                // You were correct that your code creates a Stripe customer. 
                // That is likely handled in your SubscriptionController. 
                // Adding this here centralizes the setup logic, but your existing flow also works.
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

