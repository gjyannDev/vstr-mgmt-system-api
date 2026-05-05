<?php

namespace App\Features\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Features\Auth\Services\AuthService;
use App\Features\Auth\Requests\RegisterRequest;
use App\Features\Auth\Requests\LoginRequest;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
  public function __construct(private AuthService $authService) {}

  /**
   * @OA\Get(
   *     path="/auth/me",
   *     operationId="getCurrentUser",
   *     tags={"Authentication"},
   *     summary="Get current user",
   *     description="Returns authenticated user",
   *     security={{"sanctum": {}}},
   *     @OA\Response(
   *         response=200,
   *         description="Success",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="id", type="integer", example=1),
   *             @OA\Property(property="name", type="string", example="John Doe"),
   *             @OA\Property(property="email", type="string", example="john@example.com")
   *         )
   *     )
   * )
   */

  public function me(Request $request): JsonResponse
  {
    return $this->authService->me($request);
  }

  /**
   * @OA\Post(
   *     path="/auth/register",
   *     operationId="registerUser",
   *     tags={"Authentication"},
   *     summary="Register a user",
   *     description="Register a new user",
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function register(RegisterRequest $request): JsonResponse
  {
    return $this->authService->register($request);
  }

  /**
   * @OA\Post(
   *     path="/auth/login",
   *     operationId="loginUser",
   *     tags={"Authentication"},
   *     summary="Login user",
   *     description="Authenticate a user and return token",
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function login(LoginRequest $request): JsonResponse
  {
    return $this->authService->login($request);
  }

  /**
   * @OA\Post(
   *     path="/auth/logout",
   *     operationId="logoutUser",
   *     tags={"Authentication"},
   *     summary="Logout",
   *     description="Logout authenticated user",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function logout(Request $request): JsonResponse
  {
    return $this->authService->logout($request);
  }
}
