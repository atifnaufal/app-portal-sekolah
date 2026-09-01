<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FirebaseStorageService
{
    /**
     * Upload ke Firebase Storage jika enabled & kredensial ada, fallback ke disk public.
     * Return path/URL yang bisa disimpan di DB.
     */
    public static function put(string $dir, UploadedFile $file): string
    {
        if (!config('firebase.enabled') || !config('firebase.storage_bucket')) {
            return $file->store($dir, 'public');
        }

        $cred = config('firebase.credentials');
        $bucket = config('firebase.storage_bucket');

        // cek kredensial file/json ada
        $hasCred = $cred && (is_file($cred) || str_starts_with($cred, '{'));
        if (!$hasCred) {
            return $file->store($dir, 'public');
        }

        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount($cred)
                ->withDefaultStorageBucket($bucket);

            $storage = $factory->createStorage();
            $bucketObj = $storage->getBucket();

            $name = $dir.'/'.uniqid().'_'.preg_replace('/[^A-Za-z0-9._-]/','_', $file->getClientOriginalName());
            $content = file_get_contents($file->getRealPath());

            $object = $bucketObj->upload($content, ['name'=>$name, 'predefinedAcl'=>'publicRead']);
            // public url
            return 'https://firebasestorage.googleapis.com/v0/b/'.$bucket.'/o/'.rawurlencode($name).'?alt=media';
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Firebase upload fallback: '.$e->getMessage());
            return $file->store($dir, 'public');
        }
    }

    public static function url(?string $path): ?string
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return asset('storage/'.$path);
    }
}
