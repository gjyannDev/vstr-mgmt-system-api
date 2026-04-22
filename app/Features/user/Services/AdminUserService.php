<?php

namespace App\Features\user\Services;

use App\Features\user\Repository\AdminUserRepository;
use App\Features\user\Requests\AssignAdminLocationsRequest;
use App\Features\user\Requests\StoreAdminUserRequest;
use App\Features\user\Requests\UpdateAdminUserRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserService
{
    use ApiResponse;

    public function __construct(private AdminUserRepository $adminUserRepo) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return $this->errorResponse('Authenticated super admin has no tenant assignment.', null, 422);
        }

        $search = $request->query('search');
        $createdDate = $request->query('createdDate');

        $admins = $this->adminUserRepo->list([
            'tenantId' => $tenantId,
            'search' => is_string($search) ? $search : null,
            'createdDate' => is_string($createdDate) ? $createdDate : null,
            'pageIndex' => (int) $request->query('pageIndex', 0),
            'pageSize' => (int) $request->query('pageSize', 10),
            'sort' => $request->query('sort', ['created_at' => 'desc']),
            'with' => $request->query('with', ['locations']),
            'select' => $request->query('select', ['*']),
        ]);

        return $this->successResponse('Admin users fetched successfully.', $admins);
    }

    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return $this->errorResponse('Authenticated super admin has no tenant assignment.', null, 422);
        }

        $data = $request->validated();
        $requestedLocationIds = $this->extractLocationIds($data);
        $validLocationIds = $this->adminUserRepo->resolveTenantLocationIds($tenantId, $requestedLocationIds);

        if (count($validLocationIds) !== count(array_unique($requestedLocationIds))) {
            return $this->errorResponse('One or more selected locations do not belong to your tenant.', null, 422);
        }

        $admin = $this->adminUserRepo->createAdmin([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
        ], $validLocationIds);

        return $this->successResponse('Admin user created successfully.', [
            'admin' => $admin,
        ], 201);
    }

    public function show(User $admin, Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return $this->errorResponse('Authenticated super admin has no tenant assignment.', null, 422);
        }

        if (! $this->isTenantAdmin($admin, $tenantId)) {
            return $this->errorResponse('Admin user not found.', null, 404);
        }

        $admin->load('locations');

        return $this->successResponse('Admin user fetched successfully.', [
            'admin' => $admin,
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $admin): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return $this->errorResponse('Authenticated super admin has no tenant assignment.', null, 422);
        }

        if (! $this->isTenantAdmin($admin, $tenantId)) {
            return $this->errorResponse('Admin user not found.', null, 404);
        }

        $data = $request->validated();
        $locationIds = null;

        if (array_key_exists('location_ids', $data)) {
            $requestedLocationIds = $this->extractLocationIds($data);
            $validLocationIds = $this->adminUserRepo->resolveTenantLocationIds($tenantId, $requestedLocationIds);

            if (count($validLocationIds) !== count(array_unique($requestedLocationIds))) {
                return $this->errorResponse('One or more selected locations do not belong to your tenant.', null, 422);
            }

            $locationIds = $validLocationIds;
        }

        $updateData = [];

        if (array_key_exists('name', $data)) {
            $updateData['name'] = $data['name'];
        }

        if (array_key_exists('email', $data)) {
            $updateData['email'] = $data['email'];
        }

        if (array_key_exists('password', $data)) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $updatedAdmin = $this->adminUserRepo->updateAdmin($admin, $updateData, $locationIds);

        return $this->successResponse('Admin user updated successfully.', [
            'admin' => $updatedAdmin,
        ]);
    }

    public function destroy(User $admin, Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return $this->errorResponse('Authenticated super admin has no tenant assignment.', null, 422);
        }

        if (! $this->isTenantAdmin($admin, $tenantId)) {
            return $this->errorResponse('Admin user not found.', null, 404);
        }

        if ((string) $request->user()?->id === (string) $admin->id) {
            return $this->errorResponse('Super admin cannot delete their own account through this endpoint.', null, 422);
        }

        $this->adminUserRepo->deleteAdmin($admin);

        return $this->successResponse('Admin user deleted successfully.');
    }

    public function assignLocations(AssignAdminLocationsRequest $request, User $admin): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return $this->errorResponse('Authenticated super admin has no tenant assignment.', null, 422);
        }

        if (! $this->isTenantAdmin($admin, $tenantId)) {
            return $this->errorResponse('Admin user not found.', null, 404);
        }

        $data = $request->validated();
        $requestedLocationIds = $this->extractLocationIds($data);
        $validLocationIds = $this->adminUserRepo->resolveTenantLocationIds($tenantId, $requestedLocationIds);

        if (count($validLocationIds) !== count(array_unique($requestedLocationIds))) {
            return $this->errorResponse('One or more selected locations do not belong to your tenant.', null, 422);
        }

        $updatedAdmin = $this->adminUserRepo->updateAdmin($admin, [], $validLocationIds);

        return $this->successResponse('Admin locations updated successfully.', [
            'admin' => $updatedAdmin,
        ]);
    }

    private function resolveTenantId(Request $request): ?string
    {
        $tenantId = $request->user()?->tenant_id;

        if (! is_string($tenantId) || $tenantId === '') {
            return null;
        }

        return $tenantId;
    }

    private function isTenantAdmin(User $user, string $tenantId): bool
    {
        return $user->role === 'admin' && (string) $user->tenant_id === $tenantId;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    private function extractLocationIds(array $data): array
    {
        $locationIds = $data['location_ids'] ?? [];

        if (! is_array($locationIds)) {
            return [];
        }

        return array_values(array_filter($locationIds, static fn(mixed $value): bool => is_string($value) && $value !== ''));
    }
}
