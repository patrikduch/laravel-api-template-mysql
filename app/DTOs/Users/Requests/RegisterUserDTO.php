<?php

namespace App\DTOs\Users\Requests;

use App\Http\Requests\RegisterUserRequest;

class RegisterUserDTO
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function fromRequest(RegisterUserRequest $request): self
    {
        return new self(
            first_name: $request->validated('first_name'),
            last_name: $request->validated('last_name'),
            email: $request->validated('email'),
            password: $request->validated('password')
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
