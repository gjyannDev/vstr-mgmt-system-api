<?php

namespace App\Features\Auth\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Features\Auth\Requests\StoreKioskRequest;
use App\Features\Auth\Requests\UpdateKioskRequest;
use App\Features\Auth\Requests\IndexKioskRequest;
use App\Features\Auth\Services\KioskAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Kiosk;

class KioskAdminController extends Controller
{
    public function __construct(private KioskAdminService $kioskAdminService) {}

    public function store(StoreKioskRequest $request): JsonResponse
    {
        return $this->kioskAdminService->store($request);
    }

    public function index(IndexKioskRequest $request): JsonResponse
    {
        return $this->kioskAdminService->index($request);
    }

    public function show(Request $request, Kiosk $kiosk): JsonResponse
    {
        return $this->kioskAdminService->show($request, $kiosk);
    }

    public function update(UpdateKioskRequest $request, Kiosk $kiosk): JsonResponse
    {
        return $this->kioskAdminService->update($request, $kiosk);
    }

    public function regenerate(Request $request, Kiosk $kiosk): JsonResponse
    {
        return $this->kioskAdminService->regenerate($request, $kiosk);
    }

    public function revokeTokens(Request $request, Kiosk $kiosk): JsonResponse
    {
        return $this->kioskAdminService->revokeTokens($request, $kiosk);
    }
}
