<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::latest()->get();
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'care_plan' => 'nullable|string',
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

            // Upload the contents of the temporary file and get the object back
            $object = $bucket->upload(
                file_get_contents($tempPath),
                ['name' => $fileName]
            );

            // **CRITICAL FIX:** Make the uploaded file public
            $object->update(['acl' => []], ['predefinedAcl' => 'publicRead']);

            unlink($tempPath);
            
            $profilePictureUrl = "https://storage.googleapis.com/{$bucketName}/{$fileName}";
        }

        $validated['profile_picture_url'] = $profilePictureUrl;

        Client::create($validated);

        return redirect()->route('clients.index')
                         ->with('success', 'Client added successfully!');
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
