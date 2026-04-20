<?php

namespace App\Features\Auth\Repository;

use App\Models\User;
use App\Repositories\BaseRepository;
use Laravel\Sanctum\PersonalAccessToken;

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
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    public function updateLastLogin(string $id, array $data)
    {
        return parent::update($id, $data);
    }
}
