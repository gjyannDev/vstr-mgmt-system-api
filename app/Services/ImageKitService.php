<?php

namespace App\Services;

use ImageKit\ImageKit;
use Illuminate\Support\Facades\Log;

class ImageKitService
{
  public function uploadBase64(string $base64, string $fileName = null): ?string
  {
    try {
      $publicKey = env('IMAGEKIT_PUBLIC_KEY');
      $privateKey = env('IMAGEKIT_PRIVATE_KEY');
      $urlEndpoint = env('IMAGEKIT_URL_ENDPOINT');
      Log::info('imagekit:upload:start', [
        'has_public' => ! empty($publicKey),
        'has_private' => ! empty($privateKey),
        'url_endpoint_present' => ! empty($urlEndpoint),
        'input_length' => strlen($base64),
      ]);

      if (! $publicKey || ! $privateKey) {
        Log::error('imagekit:upload:missing_keys', ['public' => (bool) $publicKey, 'private' => (bool) $privateKey]);
        return null;
      }

      $ik = new ImageKit($publicKey, $privateKey, $urlEndpoint);

      $fileName = $fileName ?? ('visitor-' . time() . '.jpg');

      // strip data URI prefix if present
      $isDataUri = false;
      if (preg_match('/^data:([^;]+);base64,/', $base64)) {
        $isDataUri = true;
        $base64ToUpload = substr($base64, strpos($base64, ',') + 1);
      } else {
        $base64ToUpload = $base64;
      }

      Log::info('imagekit:upload:prepared', [
        'fileName' => $fileName,
        'is_data_uri' => $isDataUri,
        'base64_length' => strlen($base64ToUpload),
      ]);

      $result = $ik->uploadFile([
        'file' => $base64ToUpload,
        'fileName' => $fileName,
        'useUniqueFileName' => true,
      ]);

      // Log raw result (truncated)
      $raw = is_string($result) ? $result : json_encode($result);
      $preview = strlen($raw) > 1000 ? substr($raw, 0, 1000) . '...' : $raw;
      Log::info('imagekit:upload:result', ['preview' => $preview]);

      // 1) If SDK returned a JSON string, decode and search
      if (is_string($result)) {
        $decoded = json_decode($result, true);
        if (is_array($decoded)) {
          $found = $this->findUrlRecursive($decoded, 6);
          if ($found !== null) {
            Log::info('imagekit:upload:success', ['url' => $found]);
            return $found;
          }
        }
      }

      // 2) If array, search directly
      if (is_array($result)) {
        $found = $this->findUrlRecursive($result, 6);
        if ($found !== null) {
          Log::info('imagekit:upload:success', ['url' => $found]);
          return $found;
        }
      }

      // 3) If object, cast to array and search
      if (is_object($result)) {
        $asArray = json_decode(json_encode($result), true);
        if (is_array($asArray)) {
          $found = $this->findUrlRecursive($asArray, 6);
          if ($found !== null) {
            Log::info('imagekit:upload:success', ['url' => $found]);
            return $found;
          }
        }
      }

      Log::error('imagekit:upload:no_url', ['result_preview' => $preview]);
      return null;
    } catch (\Throwable $e) {
      Log::error('imagekit:upload:exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      return null;
    }
  }

  /**
   * Recursively search array for a "url" key and return its string value.
   */
  private function findUrlRecursive(array $data, int $depth = 6): ?string
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
