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
        // Fetch all clients from the database, ordered by the newest first
        $clients = Client::latest()->get();

        // Return the 'index' view and pass the clients data to it
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Simply return the view that contains the form to create a client
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            // **FIXED CODE:** Added a specific date format validation rule
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'care_plan' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $profilePictureUrl = null;

        // 2. Handle the file upload to Firebase
        if ($request->hasFile('profile_picture')) {
            $serviceAccount = storage_path('app/firebase/firebase_credentials.json');
            $firebase = (new Factory)->withServiceAccount($serviceAccount);
            
            $storage = $firebase->createStorage();
            $bucketName = env('FIREBASE_STORAGE_BUCKET');
            $bucket = $storage->getBucket($bucketName);

            $image = $request->file('profile_picture');
            $fileName = 'profile_pictures/' . time() . '.jpg'; // Always save as jpg for consistency

            // **NEW, SIMPLER RESIZING LOGIC USING PHP's BUILT-IN GD LIBRARY**
            $tempPath = $this->resizeImageWithGD($image);

            // Upload the contents of the temporary file
            $bucket->upload(
                file_get_contents($tempPath),
                ['name' => $fileName]
            );

            // Clean up the temporary file
            unlink($tempPath);
            
            $profilePictureUrl = "https://storage.googleapis.com/{$bucketName}/{$fileName}";
        }

        // 3. Add the URL to the validated data
        $validated['profile_picture_url'] = $profilePictureUrl;

        // 4. Create a new client record
        Client::create($validated);

        // 5. Redirect back to the client list with a success message
        return redirect()->route('clients.index')
                         ->with('success', 'Client added successfully!');
    }

    /**
     * A new helper function to resize an image using GD.
     */
    private function resizeImageWithGD($file)
    {
        $maxWidth = 500;
        $maxHeight = 500;
        $sourcePath = $file->getRealPath();
        
        // Get original image info
        list($width, $height, $type) = getimagesize($sourcePath);

        // Create image resource from file
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
                // Or handle error appropriately
                return $sourcePath; 
        }

        // Create a new true color image
        $resizedImage = imagecreatetruecolor($maxWidth, $maxHeight);

        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagecolortransparent($resizedImage, imagecolorallocatealpha($resizedImage, 0, 0, 0, 127));
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }

        // Resize and crop
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height);

        // Create a temporary file to save the new image
        $tempPath = tempnam(sys_get_temp_dir(), 'resized-');
        
        // Save the resized image as a JPEG
        imagejpeg($resizedImage, $tempPath, 80); // 80% quality

        // Free up memory
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
