<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Repository\AuthRepository;
use App\Features\Auth\Requests\LoginRequest;
use App\Features\Auth\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
  public function __construct(private AuthRepository $repo) {}

  public function register(RegisterRequest $request)
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

    return response()->json([
      'user' => $user,
      'token' => $token
    ], 201);
  }

  public function login(LoginRequest $request)
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

    return response()->json([
      'user' => $user,
      'token' => $token,
    ]);
  }
}
