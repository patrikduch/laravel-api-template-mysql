<?php

namespace App\Contracts;

use App\DTOs\Auth\Requests\LoginDTO;

interface AuthServiceInterface
{
    public function login(LoginDTO $dto): ?string;
}
