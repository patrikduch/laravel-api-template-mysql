<?php

namespace App\Services;

use App\Contracts\UserServiceInterface;
use App\DTOs\Users\RegisterUserDTO;
use App\DTOs\Users\UserRegistrationResponseDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function register(RegisterUserDTO $dto): UserRegistrationResponseDTO
    {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        return new UserRegistrationResponseDTO(
            user: $user
        );
    }
}
