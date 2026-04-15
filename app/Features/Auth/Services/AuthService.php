<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Repository\AuthRepository;
use App\Features\Auth\Requests\LoginRequest;
use App\Features\Auth\Requests\RegisterRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ApiResponse;

    public function __construct(private AuthRepository $repo) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->repo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin'
        ]);

        $abilities = $user->role === 'super_admin'
            ? ['*']
            : ['admin'];

        $token = $this->repo->createToken($user, 'admin-panel', $abilities);

        return $this->successResponse('Registered successfully.', [
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->repo->findByEmail($data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (!in_array($user->role, ['admin', 'super_admin'], true)) {
            throw ValidationException::withMessages([
                'email' => ['This account is not allowed to access the admin panel.'],
            ]);
        }

        $tokenName = $data['device_name'] ?? 'admin-panel';

        $abilities = $user->role === 'super_admin'
            ? ['*']
            : ['admin'];

        $token = $this->repo->createToken($user, $tokenName, $abilities);

        return $this->successResponse('Logged in successfully.', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $this->repo->deleteCurrentAccessToken($user);
        }

        return $this->successResponse('Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse('User profile fetched successfully.', [
            'user' => $request->user(),
        ]);
    }
}
