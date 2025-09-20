<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Traits\HandlesErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use HandlesErrors;

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // For view methods, we don't use handleException since it returns redirects
        // Instead, we ensure the view always loads with safe data
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        return $this->handleDatabaseTransaction(function () use ($request) {
            $request->user()->fill($request->validated());

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $request->user()->save();
            
            return $request->user();
        }, 'Profile updated successfully!', 'Failed to update profile. Please try again.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors(), 'userDeletion');
        }

        return $this->handleDatabaseTransaction(function () use ($request) {
            $user = $request->user();

            Auth::logout();
            $user->delete();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return null;
        }, 'Account deleted successfully.', 'Failed to delete account.');
    }
}