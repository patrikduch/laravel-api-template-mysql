<?php

namespace App\DTOs\Users;

use App\Models\User;

readonly class UserResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            created_at: $user->created_at?->toIso8601String(),
            updated_at: $user->updated_at?->toIso8601String(),
        );
    }
}
