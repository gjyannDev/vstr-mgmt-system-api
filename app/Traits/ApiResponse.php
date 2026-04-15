<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
  protected function successResponse(string $message, mixed $data = null, int $status = 200): JsonResponse
  {
    return response()->json([
      'success' => true,
      'message' => $message,
      'data' => $data,
    ], $status);
  }

  protected function errorResponse(string $message, mixed $errors = null, int $status = 400): JsonResponse
  {
    $payload = [
      'success' => false,
      'message' => $message,
      'data' => null,
    ];

    if ($errors !== null) {
      $payload['errors'] = $errors;
    }

    return response()->json($payload, $status);
  }
}
