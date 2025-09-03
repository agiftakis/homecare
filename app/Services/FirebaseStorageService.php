<?php

namespace App\Services;

use Kreait\Firebase\Contract\Storage;

class FirebaseStorageService
{
    protected $storage;
    protected $bucket;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->bucket = $this->storage->getBucket();
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
            'predefinedAcl' => 'publicRead' // Make the file publicly readable
        ]);

        // For publicly readable files, mediaLink is the direct, permanent URL
        return $object->info()['mediaLink'];
    }

    /**
     * Delete an image from Firebase Storage.
     */
    public function deleteImage($url)
    {
        if (!$url) {
            return;
        }

        // CORRECTED LOGIC: Extract the file path from the URL
        $bucketName = env('FIREBASE_STORAGE_BUCKET');
        // The path is everything after the bucket name in the URL's path component
        $path = str_replace("/download/storage/v1/b/{$bucketName}/o/", '', parse_url($url, PHP_URL_PATH));
        $objectName = rawurldecode($path);

        if (empty($objectName)) {
            return;
        }
        
        try {
            $object = $this->bucket->object($objectName);
            if ($object->exists()) {
                $object->delete();
            }
        } catch (\Exception $e) {
            // Silently fail if the object doesn't exist or another error occurs
        }
    }
}