<?php

namespace App\Features\Location\Services;

use App\Features\Location\Repository\LocationRepository;
use App\Features\Location\Requests\StoreLocationRequest;
use App\Features\Location\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationService
{
    use ApiResponse;

    public function __construct(private LocationRepository $locationRepo) {}

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $createdDate = $request->query('createdDate');
        $type = $request->query('type');
        $state = $request->query('state');

        $locations = $this->locationRepo->list([
            'search' => is_string($search) ? $search : null,
            'createdDate' => is_string($createdDate) ? $createdDate : null,
            'type' => is_string($type) ? $type : null,
            'state' => is_string($state) ? $state : null,
            'pageIndex' => (int) $request->query('pageIndex', 0),
            'pageSize' => (int) $request->query('pageSize', 10),
            'sort' => $request->query('sort', ['created_at' => 'desc']),
            'with' => $request->query('with', []),
            'select' => $request->query('select', ['*']),
        ]);

        return $this->successResponse('Locations fetched successfully.', $locations);
    }

    public function listSimple(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $params = [
            'search' => is_string($search) ? $search : null,
        ];

        if ($request->user()?->tenant_id) {
            $params['tenant_id'] = $request->user()->tenant_id;
        }

        // If the current user is a regular admin (not super_admin), restrict
        // the list to locations assigned to that admin.
        $user = $request->user();
        if ($user && method_exists($user, 'isAdmin') ? $user->isAdmin() : ($user->role === 'admin')) {
            $params['admin_id'] = $user->id;
        }

        $rows = $this->locationRepo->listSimple($params);

        return $this->successResponse('Locations list fetched successfully.', $rows);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $location = $this->locationRepo->createLocation($data);

        return $this->successResponse('Location created successfully.', [
            'location' => $location,
        ], 201);
    }

    public function show(Location $location): JsonResponse
    {
        return $this->successResponse('Location fetched successfully.', [
            'location' => $location,
        ]);
    }

    public function update(UpdateLocationRequest $request, Location $location): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $updated = $this->locationRepo->updateLocation($location, $data);

        return $this->successResponse('Location updated successfully.', [
            'location' => $updated,
        ]);
    }

    public function destroy(Location $location): JsonResponse
    {
        $this->locationRepo->deleteLocation($location);

        return $this->successResponse('Location deleted successfully.');
    }
}
