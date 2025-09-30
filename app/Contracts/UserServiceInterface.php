<?php

namespace App\Contracts;

use App\DTOs\Users\RegisterUserDTO;
use App\DTOs\Users\UserRegistrationResponseDTO;

interface UserServiceInterface
{
    public function register(RegisterUserDTO $dto): UserRegistrationResponseDTO;
}
