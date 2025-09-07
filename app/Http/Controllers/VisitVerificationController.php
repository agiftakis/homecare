<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Visit;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class VisitVerificationController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    /**
     * Handle the clock-in action for a specific shift.
     */
    public function clockIn(Request $request, Shift $shift)
    {
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
        // Here you might add authorization to ensure the correct user is clocking out.
        // For now, we'll keep it simple.

        $visit->update([
            'clock_out_time' => now()
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Clocked out successfully!'
        ]);
    }
}