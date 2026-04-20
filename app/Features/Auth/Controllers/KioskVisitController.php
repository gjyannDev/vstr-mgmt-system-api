<?php

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Requests\StoreKioskVisitRequest;
use App\Features\Auth\Services\KioskVisitService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class KioskVisitController extends Controller
{
    public function __construct(private KioskVisitService $kioskVisitService) {}

    public function store(StoreKioskVisitRequest $request): JsonResponse
    {
        return $this->kioskVisitService->store($request);
    }
}
