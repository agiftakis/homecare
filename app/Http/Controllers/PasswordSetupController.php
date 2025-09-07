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
        $user = User::where('password_setup_token', hash('sha256', $token))->first();

        // ✅ CORRECTED LOGIC: We only need to check if the user exists and the token is not expired.
        if (!$user || $user->password_setup_expires_at->isPast()) {
            if ($user) {
                $user->forceFill(['password_setup_token' => null, 'password_setup_expires_at' => null])->save();
            }
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

        $user = User::where('password_setup_token', hash('sha256', $request->token))->first();

        // ✅ CORRECTED LOGIC: Perform the same corrected check here.
        if (!$user || $user->password_setup_expires_at->isPast()) {
            return view('auth.invalid-link');
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'password_setup_token' => null,
            'password_setup_expires_at' => null,
        ])->save();

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome! Your password has been set successfully.');
    }
}