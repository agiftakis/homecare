<?php

namespace App\Services;

use Kreait\Firebase\Factory;

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
