<?php

namespace App\Contracts;

use App\DTOs\Users\Requests\RegisterUserDTO;
use App\DTOs\Users\Responses\UserRegistrationResponseDTO;

interface UserServiceInterface
{
    public function register(RegisterUserDTO $dto): UserRegistrationResponseDTO;
}
