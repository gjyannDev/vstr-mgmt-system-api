<?php

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Requests\ActivateKioskRequest;
use App\Features\Auth\Services\KioskAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KioskAuthController extends Controller
{
    public function __construct(private KioskAuthService $kioskAuthService) {}

    public function activate(ActivateKioskRequest $request): JsonResponse
    {
        return $this->kioskAuthService->activate($request);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->kioskAuthService->me($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->kioskAuthService->logout($request);
    }
}
