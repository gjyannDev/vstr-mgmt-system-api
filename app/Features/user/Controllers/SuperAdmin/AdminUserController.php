<?php

namespace App\Features\user\Controllers\SuperAdmin;

use App\Features\user\Requests\StoreAdminUserRequest;
use App\Features\user\Requests\UpdateAdminUserRequest;
use App\Features\user\Requests\AssignAdminLocationsRequest;
use App\Features\user\Services\AdminUserService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct(private AdminUserService $adminUserService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->adminUserService->index($request);
    }

    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        return $this->adminUserService->store($request);
    }

    public function show(User $admin, Request $request): JsonResponse
    {
        return $this->adminUserService->show($admin, $request);
    }

    public function update(UpdateAdminUserRequest $request, User $admin): JsonResponse
    {
        return $this->adminUserService->update($request, $admin);
    }

    public function assignLocations(AssignAdminLocationsRequest $request, User $admin): JsonResponse
    {
        return $this->adminUserService->assignLocations($request, $admin);
    }

    public function destroy(User $admin, Request $request): JsonResponse
    {
        return $this->adminUserService->destroy($admin, $request);
    }
}
