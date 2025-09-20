<?php

namespace App\Http\Controllers;

use App\Events\VisitStatusChanged;
use App\Models\Shift;
use App\Models\Visit;
use App\Services\FirebaseStorageService;
use App\Traits\HandlesErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class VisitVerificationController extends Controller
{
    use HandlesErrors;

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
        try {
            // Authorization: Ensure the logged-in user is the caregiver assigned to this shift.
            if (!$shift->caregiver || Auth::user()->id !== $shift->caregiver->user_id) {
                return response()->view('errors.403', [
                    'title' => 'Access Denied',
                    'message' => 'You are not assigned to this shift.'
                ], 403);
            }

            // DATE VALIDATION: Check if the shift date is today or in the past
            $userTimezone = Auth::user()->agency?->timezone ?? 'UTC';
            $today = Carbon::today($userTimezone);
            $shiftDate = Carbon::parse($shift->start_time)->setTimezone($userTimezone)->startOfDay();

            // If shift is in the future, pass a flag to the view
            $isShiftDateValid = $shiftDate->lessThanOrEqualTo($today);

            // Find the existing visit record for this shift, if one exists.
            $visit = Visit::where('shift_id', $shift->id)->first();

            return view('visits.show', compact('shift', 'visit', 'isShiftDateValid'));
        } catch (\Exception $e) {
            return $this->handleException($e, 'Unable to load visit verification page.', 'visit_verification_show');
        }
    }

    /**
     * Handle the clock-in action for a specific shift.
     */
    public function clockIn(Request $request, Shift $shift)
    {
        // Authorization check
        if (!$shift->caregiver || Auth::user()->id !== $shift->caregiver->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to clock in for this shift.',
                'error_code' => 'ACCESS_DENIED'
            ], 403);
        }

        // DATE VALIDATION: Prevent clock-in for future shifts
        try {
            $userTimezone = Auth::user()->agency?->timezone ?? 'UTC';
            $today = Carbon::today($userTimezone);
            $shiftDate = Carbon::parse($shift->start_time)->setTimezone($userTimezone)->startOfDay();

            if ($shiftDate->greaterThan($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The scheduled shift date has not arrived yet!',
                    'error_code' => 'FUTURE_SHIFT_DATE'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Date validation error. Please try again.',
                'error_code' => 'DATE_VALIDATION_ERROR'
            ], 500);
        }

        // Validate signature data
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        }

        return $this->handleDatabaseTransaction(function () use ($request, $shift) {
            // Decode and process signature
            try {
                $signatureDataUrl = $request->input('signature');

                // Validate base64 signature format
                if (!preg_match('/^data:image\/\w+;base64,/', $signatureDataUrl)) {
                    throw new \Exception('Invalid signature format');
                }

                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureDataUrl));

                if ($imageData === false) {
                    throw new \Exception('Failed to decode signature data');
                }

                $tempFilePath = tempnam(sys_get_temp_dir(), 'signature');
                if (!$tempFilePath || !file_put_contents($tempFilePath, $imageData)) {
                    throw new \Exception('Failed to create temporary signature file');
                }

                $file = new UploadedFile($tempFilePath, 'signature.png', 'image/png', null, true);
                $caregiverName = $shift->caregiver->first_name . '_' . $shift->caregiver->last_name ?? 'caregiver';

                $documentInfo = $this->firebaseStorageService->uploadDocument($file, $caregiverName, 'ClockIn_Signature');

                // Clean up temp file
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }

                // Create the Visit record in the database
                $visit = Visit::create([
                    'shift_id' => $shift->id,
                    'agency_id' => $shift->agency_id, // Important for multi-tenancy
                    'clock_in_time' => now(),
                    'signature_path' => $documentInfo['firebase_path'],
                ]);

                // Update the shift status to 'in_progress'
                $shift->update(['status' => 'in_progress']);

                // Dispatch real-time event to notify the client
                try {
                    VisitStatusChanged::dispatch($shift, 'in_progress', $visit);
                } catch (\Exception $e) {
                    $this->logError($e, 'visit_status_event_dispatch');
                    // Continue even if event dispatch fails
                }

                return $visit;
            } catch (\Exception $e) {
                // Clean up temp file if it exists
                if (isset($tempFilePath) && file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
                throw new \Exception('Signature processing failed: ' . $e->getMessage());
            }
        }, 'Clocked in successfully!', 'Failed to clock in. Please try again.');
    }

    /**
     * Handle the clock-out action for a specific visit.
     */
    public function clockOut(Request $request, Visit $visit)
    {
        // Authorization check
        if (!$visit->shift || !$visit->shift->caregiver || Auth::user()->id !== $visit->shift->caregiver->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to clock out for this visit.',
                'error_code' => 'ACCESS_DENIED'
            ], 403);
        }

        // DATE VALIDATION: Prevent clock-out for future shifts
        try {
            $userTimezone = Auth::user()->agency?->timezone ?? 'UTC';
            $today = Carbon::today($userTimezone);
            $shiftDate = Carbon::parse($visit->shift->start_time)->setTimezone($userTimezone)->startOfDay();

            if ($shiftDate->greaterThan($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The scheduled shift date has not arrived yet!',
                    'error_code' => 'FUTURE_SHIFT_DATE'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Date validation error. Please try again.',
                'error_code' => 'DATE_VALIDATION_ERROR'
            ], 500);
        }

        // Validate signature and progress notes
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string',
            'progress_notes' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        }

        return $this->handleDatabaseTransaction(function () use ($request, $visit) {
            // Decode and process signature
            try {
                $signatureDataUrl = $request->input('signature');

                // Validate base64 signature format
                if (!preg_match('/^data:image\/\w+;base64,/', $signatureDataUrl)) {
                    throw new \Exception('Invalid signature format');
                }

                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureDataUrl));

                if ($imageData === false) {
                    throw new \Exception('Failed to decode signature data');
                }

                $tempFilePath = tempnam(sys_get_temp_dir(), 'signature_out');
                if (!$tempFilePath || !file_put_contents($tempFilePath, $imageData)) {
                    throw new \Exception('Failed to create temporary signature file');
                }

                $file = new UploadedFile($tempFilePath, 'signature_out.png', 'image/png', null, true);
                $caregiverName = $visit->shift->caregiver->first_name . '_' . $visit->shift->caregiver->last_name ?? 'caregiver';

                $documentInfo = $this->firebaseStorageService->uploadDocument($file, $caregiverName, 'ClockOut_Signature');

                // Clean up temp file
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }

                // Update the visit record with clock-out data and notes
                $visit->update([
                    'progress_notes' => $request->input('progress_notes'),
                    'clock_out_time' => now(),
                    'clock_out_signature_path' => $documentInfo['firebase_path'],
                ]);

                // Update the shift status to 'completed'
                $visit->shift->update(['status' => 'completed']);

                // Dispatch real-time event to notify the client
                try {
                    VisitStatusChanged::dispatch($visit->shift, 'completed', $visit);
                } catch (\Exception $e) {
                    $this->logError($e, 'visit_status_event_dispatch');
                    // Continue even if event dispatch fails
                }

                return $visit;
            } catch (\Exception $e) {
                // Clean up temp file if it exists
                if (isset($tempFilePath) && file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
                throw new \Exception('Signature processing failed: ' . $e->getMessage());
            }
        }, 'Clocked out successfully!', 'Failed to clock out. Please try again.');
    }
}
