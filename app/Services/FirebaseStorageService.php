<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Illuminate\Http\UploadedFile;

class FirebaseStorageService
{
    protected $storage;
    protected $bucket;

    public function __construct()
    {
        // STEP 1: Manually build the connection using the direct path to your credentials.
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'));

        $this->storage = $factory->createStorage();

        // STEP 2: Manually specify the exact bucket name we know is correct.
        $this->bucket = $this->storage->getBucket(env('FIREBASE_STORAGE_BUCKET'));
    }

    /**
     * Upload an image to Firebase Storage.
     */
    public function uploadImage($image)
    {
        $fileName = 'profile_pictures/' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        $imageStream = fopen($image->getRealPath(), 'r');

        $object = $this->bucket->upload($imageStream, [
            'name' => $fileName,
            'predefinedAcl' => 'publicRead'
        ]);

        return $object->info()['mediaLink'];
    }

    // ADD CODE HERE - after the uploadProfilePicture method
    /**
     * Upload a document with descriptive filename
     */
    public function uploadDocument(UploadedFile $file, string $caregiverName, string $documentType, string $folderPath = 'caregiver_documents'): array
    {
        // Clean the caregiver name for filename
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $caregiverName);
        $cleanDocumentType = ucfirst(str_replace('_', '_', $documentType));

        // Create descriptive filename: John_Doe_Certification.pdf
        $extension = $file->getClientOriginalExtension();
        $descriptiveFilename = $cleanName . '_' . $cleanDocumentType . '.' . $extension;

        // Create unique Firebase path to avoid conflicts
        $uniqueId = uniqid();
        $firebasePath = $folderPath . '/' . $uniqueId . '_' . $descriptiveFilename;

        $bucket = $this->storage->getBucket(env('FIREBASE_STORAGE_BUCKET'));
        $bucket->upload(
            file_get_contents($file->getRealPath()),
            ['name' => $firebasePath]
        );

        return [
            'firebase_path' => $firebasePath,
            'descriptive_filename' => $descriptiveFilename
        ];
    }
    /**
     * Delete an image from Firebase Storage.
     */
    public function deleteImage($url)
    {
        if (!$url) {
            return;
        }

        try {
            // CORRECTED LOGIC: Manually parse the URL to get the object name
            $path = parse_url($url, PHP_URL_PATH);
            $objectNamePosition = strpos($path, '/o/');

            if ($objectNamePosition === false) {
                return; // Can't find the object name
            }

            $urlencodedObjectName = substr($path, $objectNamePosition + 3);
            $objectName = rawurldecode($urlencodedObjectName);

            if (empty($objectName)) {
                return;
            }

            $object = $this->bucket->object($objectName);
            if ($object->exists()) {
                $object->delete();
            }
        } catch (\Exception $e) {
            // Silently fail if the object doesn't exist
        }
    }
}
