<?php

namespace App\Features\Visits\Controllers\Api;

use App\Features\Visits\Requests\StoreVisitResponseRequest;
use App\Features\Visits\Services\VisitResponseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class VisitResponseController extends Controller
{
    public function __construct(private VisitResponseService $service) {}

    public function store(StoreVisitResponseRequest $request): JsonResponse
    {
        return $this->service->store($request);
    }

    public function show(string $visitId): JsonResponse
    {
        return $this->service->showById($visitId);
    }

    public function showByQr(string $qrCode): JsonResponse
    {
        return $this->service->showByQr($qrCode);
    }
}
