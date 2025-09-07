<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordSetupController extends Controller
{
    /**
     * Show the password setup form.
     */
    public function show(string $token)
    {
        // Find the user by the hashed version of the token
        $user = User::where('password_setup_token', hash('sha256', $token))->first();

        // 1. Check if the token is valid, has not expired, and a password isn't already set
        if (!$user || $user->password_setup_expires_at->isPast() || $user->password !== null) {
            // Invalidate the token to prevent reuse
            if ($user) {
                $user->forceFill(['password_setup_token' => null, 'password_setup_expires_at' => null])->save();
            }
            // Show a view indicating the link is invalid or expired
            return view('auth.invalid-link');
        }

        return view('auth.setup-password', ['token' => $token]);
    }

    /**
     * Store the new password.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Find the user by the hashed version of the token again for security
        $user = User::where('password_setup_token', hash('sha256', $request->token))->first();

        // Perform the same validation checks as the show method
        if (!$user || $user->password_setup_expires_at->isPast() || $user->password !== null) {
            return view('auth.invalid-link');
        }

        // Update the user's record
        $user->forceFill([
            'password' => Hash::make($request->password),
            'email_verified_at' => now(), // Mark email as verified since they used the link
            'password_setup_token' => null, // Invalidate the token
            'password_setup_expires_at' => null,
        ])->save();

        // Log the new user in
        Auth::login($user);

        // Redirect them to their dashboard
        return redirect()->route('dashboard')->with('success', 'Welcome! Your password has been set successfully.');
    }
}