<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Intervention\Image\Facades\Image; // <-- This is the corrected line for version 3

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
            'date_of_birth' => 'required|date',
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
            // Change the file extension to .jpg since we are encoding it as a JPEG
            $fileName = 'profile_pictures/' . time() . '.jpg';

            // **OPTIMIZATION:** Resize and compress the image
            // **FIXED CODE:** Call the make() method on the imported Image facade
            $resizedImage = Image::make($image)
                ->fit(500, 500, function ($constraint) {
                    $constraint->upsize(); // Prevents small images from being enlarged
                })
                ->encode('jpg', 80); // Encode as JPG with 80% quality

            $bucket->upload(
                $resizedImage, // <-- Upload the resized image data
                ['name' => $fileName]
            );
            
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