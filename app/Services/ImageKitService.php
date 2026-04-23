<?php

namespace App\Services;

use Imagekit\Imagekit;

class ImageKitService
{
    public function uploadBase64(string $base64, string $fileName = null): ?string
    {
        try {
            $publicKey = env('IMAGEKIT_PUBLIC_KEY');
            $privateKey = env('IMAGEKIT_PRIVATE_KEY');
            $urlEndpoint = env('IMAGEKIT_URL_ENDPOINT');

            if (! $publicKey || ! $privateKey) {
                return null;
            }

            $ik = new Imagekit($publicKey, $privateKey, $urlEndpoint);

            $fileName = $fileName ?? ('visitor-' . time() . '.jpg');

            $result = $ik->uploadFile([
                'file' => $base64,
                'fileName' => $fileName,
                'useUniqueFileName' => true,
            ]);

            // 1) If SDK returned a JSON string, decode and search
            if (is_string($result)) {
                $decoded = json_decode($result, true);
                if (is_array($decoded)) {
                    return $this->findUrlRecursive($decoded);
                }
            }

            // 2) If array, search directly
            if (is_array($result)) {
                return $this->findUrlRecursive($result);
            }

            // 3) If object, cast to array and search
            if (is_object($result)) {
                $asArray = json_decode(json_encode($result), true);
                if (is_array($asArray)) {
                    return $this->findUrlRecursive($asArray);
                }
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Recursively search array for a "url" key and return its string value.
     */
    private function findUrlRecursive(array $data, int $depth = 3): ?string
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && strtolower($key) === 'url' && is_string($value) && $value !== '') {
                return $value;
            }

            if (is_array($value) && $depth > 0) {
                $found = $this->findUrlRecursive($value, $depth - 1);

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
