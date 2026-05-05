<?php

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Requests\StoreKioskVisitRequest;
use App\Features\Auth\Services\KioskVisitService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class KioskVisitController extends Controller
{
    public function __construct(private KioskVisitService $kioskVisitService) {}

    /**
     * @OA@Post(
     *     path="/kiosk/visits",
     *     operationId="storeKioskVisit",
     *     tags={"KioskVisits"},
     *     summary="Create a kiosk visit",
     *     description="Create a visit from a kiosk client",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function store(StoreKioskVisitRequest $request): JsonResponse
    {
        return $this->kioskVisitService->store($request);
    }
}
