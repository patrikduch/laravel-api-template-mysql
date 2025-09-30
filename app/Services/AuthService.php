<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\DTOs\Auth\Requests\LoginDTO;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService implements AuthServiceInterface
{
    /**
     * Authenticate user and return token.
     */
    public function login(LoginDTO $dto): ?string
    {
        // Attempt authentication
        if (!Auth::attempt(['email' => $dto->email, 'password' => $dto->password])) {
            return null;
        }

        /** @var User $user */
        $user = Auth::user();

        // Generate Sanctum token
        return $user->createToken('auth_token')->plainTextToken;
    }
}
