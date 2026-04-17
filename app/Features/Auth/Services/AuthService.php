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

    private const ROLE_ABILITIES = [
        'super_admin' => ['*'],
        'admin' => ['admin:access'],
        'kiosk' => ['kiosk:access'],
        'customer' => ['customer:access'],
    ];

    private function resolveAbilities(string $role): array
    {
        $abilities = self::ROLE_ABILITIES[$role] ?? [];

        if ($abilities === ['*']) {
            return $abilities;
        }

        return ['auth:api', 'role:' . $role, ...$abilities];
    }

    public function __construct(private AuthRepository $repo) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->repo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
        ]);

        $abilities = $this->resolveAbilities($user->role);

        $token = $this->repo->createToken($user, 'access-token', $abilities);

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

        $tokenName = $data['device_name'] ?? 'access-token';

        $abilities = $this->resolveAbilities($user->role);

        $token = $this->repo->createToken($user, $tokenName, $abilities);

        $this->repo->update($user->id, ['last_login_at' => now()]);

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
