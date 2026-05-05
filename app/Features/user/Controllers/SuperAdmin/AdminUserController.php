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
use OpenApi\Annotations as OA;

class AdminUserController extends Controller
{
  public function __construct(private AdminUserService $adminUserService) {}

  /**
   * @OA\Get(
   *     path="/super-admin/admins",
   *     operationId="listAdmins",
   *     tags={"Admins"},
   *     summary="List admin users",
   *     description="List administrators (super-admin)",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function index(Request $request): JsonResponse
  {
    return $this->adminUserService->index($request);
  }

  /**
   * @OA\Post(
   *     path="/super-admin/admins",
   *     operationId="createAdmin",
   *     tags={"Admins"},
   *     summary="Create admin user",
   *     description="Create a new admin user",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function store(StoreAdminUserRequest $request): JsonResponse
  {
    return $this->adminUserService->store($request);
  }

  /**
   * @OA\Get(
   *     path="/super-admin/admins/{admin}",
   *     operationId="showAdmin",
   *     tags={"Admins"},
   *     summary="Show admin user",
   *     description="Retrieve admin user details",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="admin",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function show(User $admin, Request $request): JsonResponse
  {
    return $this->adminUserService->show($admin, $request);
  }

  /**
   * @OA\Put(
   *     path="/super-admin/admins/{admin}",
   *     operationId="updateAdmin",
   *     tags={"Admins"},
   *     summary="Update admin user",
   *     description="Update an admin user",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="admin",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function update(UpdateAdminUserRequest $request, User $admin): JsonResponse
  {
    return $this->adminUserService->update($request, $admin);
  }

  /**
   * @OA\Put(
   *     path="/super-admin/admins/{admin}/locations",
   *     operationId="assignAdminLocations",
   *     tags={"Admins"},
   *     summary="Assign admin locations",
   *     description="Assign locations to an admin user",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="admin",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function assignLocations(AssignAdminLocationsRequest $request, User $admin): JsonResponse
  {
    return $this->adminUserService->assignLocations($request, $admin);
  }

  /**
   * @OA\Delete(
   *     path="/super-admin/admins/{admin}",
   *     operationId="deleteAdmin",
   *     tags={"Admins"},
   *     summary="Delete admin user",
   *     description="Delete an admin user",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="admin",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function destroy(User $admin, Request $request): JsonResponse
  {
    return $this->adminUserService->destroy($admin, $request);
  }
}
