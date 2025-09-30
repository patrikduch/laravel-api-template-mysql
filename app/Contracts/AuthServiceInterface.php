<?php

namespace App\Contracts;

use App\DTOs\Auth\LoginDTO;

interface AuthServiceInterface
{
    public function login(LoginDTO $dto): ?string;
}
