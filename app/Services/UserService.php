<?php

namespace App\Services;

use App\Contracts\UserServiceInterface;
use App\DTOs\Users\Requests\RegisterUserDTO;
use App\DTOs\Users\Responses\UserRegistrationResponseDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function register(RegisterUserDTO $dto): UserRegistrationResponseDTO
    {
        $user = User::create([
            'first_name' => $dto->first_name,
            'last_name' => $dto->last_name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        return new UserRegistrationResponseDTO(
            user: $user
        );
    }
}
