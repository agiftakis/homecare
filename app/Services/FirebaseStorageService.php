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
     * Upload a profile picture and return the Firebase path (not URL)
     */
    public function uploadProfilePicture(UploadedFile $file, string $folderPath = 'profile_pictures'): string
    {
        $fileName = $folderPath . '/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $imageStream = fopen($file->getRealPath(), 'r');

        $this->bucket->upload($imageStream, [
            'name' => $fileName,
            'predefinedAcl' => 'publicRead'
        ]);

        return $fileName; // Return the Firebase path, not the URL
    }

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

        $documentStream = fopen($file->getRealPath(), 'r');

        $this->bucket->upload($documentStream, [
            'name' => $firebasePath,
            'predefinedAcl' => 'publicRead'
        ]);

        return [
            'firebase_path' => $firebasePath,
            'descriptive_filename' => $descriptiveFilename
        ];
    }

    /**
     * Get public URL for a file path
     */
    public function getPublicUrl(string $firebasePath): string
    {
        try {
            $object = $this->bucket->object($firebasePath);
            if ($object->exists()) {
                return $object->info()['mediaLink'];
            }
        } catch (\Exception $e) {
            // Return empty string if file doesn't exist
        }
        return '';
    }
    /**
     * Delete a file from Firebase Storage using file path
     */
    public function deleteFile(string $firebasePath): bool
    {
        try {
            $object = $this->bucket->object($firebasePath);
            if ($object->exists()) {
                $object->delete();
                return true;
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        return false;
    }

    /**
     * Delete profile picture (backward compatibility)
     */
    public function deleteProfilePicture(string $firebasePath): bool
    {
        return $this->deleteFile($firebasePath);
    }

    /**
     * LEGACY METHOD: Upload an image to Firebase Storage (for backward compatibility)
     * This method returns a URL - use uploadProfilePicture() for new code
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

    /**
     * LEGACY METHOD: Delete an image from Firebase Storage using URL
     * This method works with URLs - use deleteFile() for new code
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
