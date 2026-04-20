<?php

namespace App\Features\Auth\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Features\Auth\Requests\StoreKioskRequest;
use App\Features\Auth\Services\KioskAdminService;
use Illuminate\Http\JsonResponse;

class KioskAdminController extends Controller
{
    public function __construct(private KioskAdminService $kioskAdminService) {}

    public function store(StoreKioskRequest $request): JsonResponse
    {
        return $this->kioskAdminService->store($request);
    }
}
