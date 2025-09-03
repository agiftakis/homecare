<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use App\Services\FirebaseStorageService; // Use our service
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CaregiverController extends Controller
{
    protected $firebaseStorageService;

    // Inject the service we already built
    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        $caregivers = Caregiver::latest()->get();
        return view('caregivers.index', compact('caregivers'));
    }

    public function create()
    {
        return view('caregivers.create');
    }

    public function store(Request $request)
    {
        $agencyId = Auth::user()->agency_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // Correct multi-tenant validation
            'email' => [
                'required',
                'email',
                Rule::unique('caregivers')->where(fn ($query) => $query->where('agency_id', $agencyId)),
            ],
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'certifications' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture_url'] = $this->firebaseStorageService->uploadImage($request->file('profile_picture'));
        }

        Caregiver::create($validated);

        return redirect()->route('caregivers.index')->with('success', 'Caregiver added successfully!');
    }

    public function edit(Caregiver $caregiver)
    {
        // Add authorization
        $this->authorize('update', $caregiver);
        
        return view('caregivers.edit', compact('caregiver'));
    }

    public function update(Request $request, Caregiver $caregiver)
    {
        // Add authorization
        $this->authorize('update', $caregiver);

        $agencyId = Auth::user()->agency_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // Correct multi-tenant validation
            'email' => [
                'required',
                'email',
                Rule::unique('caregivers')->where(fn ($query) => $query->where('agency_id', $agencyId))->ignore($caregiver->id),
            ],
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'certifications' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $this->firebaseStorageService->deleteImage($caregiver->profile_picture_url);
            $validated['profile_picture_url'] = $this->firebaseStorageService->uploadImage($request->file('profile_picture'));
        }

        $caregiver->update($validated);

        return redirect()->route('caregivers.index')->with('success', 'Caregiver updated successfully!');
    }

    public function destroy(Caregiver $caregiver)
    {
        // Add authorization
        $this->authorize('delete', $caregiver);

        $this->firebaseStorageService->deleteImage($caregiver->profile_picture_url);
        $caregiver->delete();

        return redirect()->route('caregivers.index')->with('success', 'Caregiver deleted successfully.');
    }
}