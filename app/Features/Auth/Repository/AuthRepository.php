<?php

namespace App\Features\Auth\Repository;

use App\Models\User;
use App\Repositories\BaseRepository;

class AuthRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function createToken(User $user, string $tokenName, array $abilities = ['*']): string
    {
        return $user->createToken($tokenName, $abilities)->plainTextToken;
    }

    public function deleteCurrentAccessToken(User $user): void
    {
        $user->tokens()->delete();
    }

    public function updateLastLogin(int $id, array $data)
    {
        return parent::update($id, $data);
    }
}
