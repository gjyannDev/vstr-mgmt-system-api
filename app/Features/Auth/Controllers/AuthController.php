<?php

namespace App\Features\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Features\Auth\Services\AuthService;
use App\Features\Auth\Requests\RegisterRequest;
use App\Features\Auth\Requests\LoginRequest;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->authService->register($request);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->authService->login($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->authService->logout($request);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->authService->me($request);
    }
}
