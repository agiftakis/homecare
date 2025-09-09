<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Visit;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VisitVerificationController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }


    /**
     * Show the verification page for a specific shift.
     */
    public function show(Shift $shift)
    {
        // Authorization: Ensure the logged-in user is the caregiver assigned to this shift.
        // NOTE: In a real app with different user roles, you might use a formal Policy here.
        if (Auth::user()->id !== $shift->caregiver->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // ✅ DATE VALIDATION: Check if the shift date is today or in the past
        $userTimezone = Auth::user()->agency?->timezone ?? 'UTC';
        $today = Carbon::today($userTimezone);
        $shiftDate = Carbon::parse($shift->start_time)->setTimezone($userTimezone)->startOfDay();
        
        // If shift is in the future, pass a flag to the view
        $isShiftDateValid = $shiftDate->lessThanOrEqualTo($today);

        // Find the existing visit record for this shift, if one exists.
        $visit = Visit::where('shift_id', $shift->id)->first();

        return view('visits.show', compact('shift', 'visit', 'isShiftDateValid'));
    }
    /**
     * Handle the clock-in action for a specific shift.
     */
    public function clockIn(Request $request, Shift $shift)
    {
        // ✅ DATE VALIDATION: Prevent clock-in for future shifts
        $userTimezone = Auth::user()->agency?->timezone ?? 'UTC';
        $today = Carbon::today($userTimezone);
        $shiftDate = Carbon::parse($shift->start_time)->setTimezone($userTimezone)->startOfDay();
        
        if ($shiftDate->greaterThan($today)) {
            return response()->json([
                'success' => false, 
                'message' => 'The Scheduled Shift Date Has Not Arrived Yet!'
            ], 422);
        }

        // 1. Validate that the signature data is present
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // 2. Decode the Base64 signature and save it to Firebase
        $signatureDataUrl = $request->input('signature');

        // Remove the "data:image/png;base64," part
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureDataUrl));

        // Create a temporary file to upload
        $tempFilePath = tempnam(sys_get_temp_dir(), 'signature');
        file_put_contents($tempFilePath, $imageData);

        // Create an UploadedFile instance from the temp file
        $file = new UploadedFile($tempFilePath, 'signature.png', 'image/png', null, true);

        // Use our Firebase service to upload the signature
        $caregiverName = $shift->caregiver->full_name ?? 'caregiver';
        $documentInfo = $this->firebaseStorageService->uploadDocument($file, $caregiverName, 'Signature');

        // Clean up the temporary file
        unlink($tempFilePath);

        // 3. Create the Visit record in the database
        $visit = Visit::create([
            'shift_id' => $shift->id,
            'agency_id' => $shift->agency_id, // Important for multi-tenancy
            'clock_in_time' => now(),
            'signature_path' => $documentInfo['firebase_path'],
        ]);

        // ✅ --- FIX: Update the shift status to 'in_progress' ---
        $shift->update(['status' => 'in_progress']);
        // ---------------------------------------------------------

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully!',
            'visit_id' => $visit->id,
        ]);
    }

    /**
     * Handle the clock-out action for a specific visit.
     */
    public function clockOut(Request $request, Visit $visit)
    {
        // ✅ DATE VALIDATION: Prevent clock-out for future shifts
        $userTimezone = Auth::user()->agency?->timezone ?? 'UTC';
        $today = Carbon::today($userTimezone);
        $shiftDate = Carbon::parse($visit->shift->start_time)->setTimezone($userTimezone)->startOfDay();
        
        if ($shiftDate->greaterThan($today)) {
            return response()->json([
                'success' => false, 
                'message' => 'The Scheduled Shift Date Has Not Arrived Yet!'
            ], 422);
        }

        // 1. Validate the signature data
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // 2. Decode the Base64 signature and save it to Firebase
        $signatureDataUrl = $request->input('signature');
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureDataUrl));

        $tempFilePath = tempnam(sys_get_temp_dir(), 'signature_out');
        file_put_contents($tempFilePath, $imageData);

        $file = new UploadedFile($tempFilePath, 'signature_out.png', 'image/png', null, true);

        // Use our Firebase service to upload the clock-out signature
        $caregiverName = $visit->shift->caregiver->full_name ?? 'caregiver';
        $documentInfo = $this->firebaseStorageService->uploadDocument($file, $caregiverName, 'SignatureOut');

        unlink($tempFilePath);

        // 3. Update the Visit record with the clock-out time and new signature path
        $visit->update([
            'clock_out_time' => now(),
            'clock_out_signature_path' => $documentInfo['firebase_path'],
        ]);

        // ✅ --- FIX: Update the shift status to 'completed' ---
        $visit->shift->update(['status' => 'completed']);
        // -----------------------------------------------------

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully!'
        ]);
    }
}