<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class CaregiverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $caregivers = Caregiver::latest()->get();
        return view('caregivers.index', compact('caregivers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('caregivers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:caregivers,email',
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'certifications' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $profilePictureUrl = null;

        if ($request->hasFile('profile_picture')) {
            $serviceAccount = storage_path('app/firebase/firebase_credentials.json');
            $firebase = (new Factory)->withServiceAccount($serviceAccount);
            
            $storage = $firebase->createStorage();
            $bucketName = env('FIREBASE_STORAGE_BUCKET');
            $bucket = $storage->getBucket($bucketName);

            $image = $request->file('profile_picture');
            $fileName = 'profile_pictures/' . time() . '.jpg';

            $tempPath = $this->resizeImageWithGD($image);

            $bucket->upload(
                file_get_contents($tempPath),
                ['name' => $fileName]
            );

            unlink($tempPath);
            
            $profilePictureUrl = "https://storage.googleapis.com/{$bucketName}/{$fileName}";
        }

        $validated['profile_picture_url'] = $profilePictureUrl;

        Caregiver::create($validated);

        return redirect()->route('caregivers.index')
                         ->with('success', 'Caregiver added successfully!');
    }

    private function resizeImageWithGD($file)
    {
        $maxWidth = 500;
        $maxHeight = 500;
        $sourcePath = $file->getRealPath();
        
        list($width, $height, $type) = getimagesize($sourcePath);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return $sourcePath; 
        }

        $resizedImage = imagecreatetruecolor($maxWidth, $maxHeight);

        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagecolortransparent($resizedImage, imagecolorallocatealpha($resizedImage, 0, 0, 0, 127));
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }

        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height);

        $tempPath = tempnam(sys_get_temp_dir(), 'resized-');
        
        imagejpeg($resizedImage, $tempPath, 80);

        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return $tempPath;
    }

    // ... (Leave show, edit, update, destroy methods empty for now)
}
