<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Traits\HandlesErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Client;
use App\Models\Caregiver;

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
            $user = $request->user();
            $user->fill($request->validated());

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
            
            // ============================================
            // NEWLY ADDED: Propagate changes to Client/Caregiver records
            // ============================================
            $this->propagateProfileChanges($user, $request->validated());
            // ============================================
            // END OF NEWLY ADDED CODE
            // ============================================
            
            return $user;
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

    // ============================================
    // NEWLY ADDED METHOD BELOW
    // ============================================
    /**
     * Propagate profile changes to Client or Caregiver records
     */
    protected function propagateProfileChanges($user, array $validated): void
    {
        // Only propagate for clients and caregivers
        if (!in_array($user->role, ['client', 'caregiver'])) {
            return;
        }

        // Parse the name into first_name and last_name
        $nameParts = explode(' ', $validated['name'], 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $validated['email'],
        ];

        // Update the appropriate model based on role
        if ($user->role === 'client') {
            Client::where('user_id', $user->id)->update($updateData);
        } elseif ($user->role === 'caregiver') {
            Caregiver::where('user_id', $user->id)->update($updateData);
        }
    }
    // ============================================
    // END OF NEWLY ADDED METHOD
    // ============================================
}