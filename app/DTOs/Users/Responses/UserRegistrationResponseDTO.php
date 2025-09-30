<?php

namespace App\DTOs\Users\Responses;

use App\Models\User;

class UserRegistrationResponseDTO
{
    public function __construct(
        public readonly User $user,
        public readonly ?string $accessToken = null,
        public readonly string $tokenType = 'Bearer'
    ) {}

    /**
     * Convert the DTO to a strictly typed array
     */
    public function toArray(): array
    {
        $userData = [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'created_at' => $this->user->created_at?->toIso8601String(),
            'updated_at' => $this->user->updated_at?->toIso8601String(),
        ];

        if ($this->accessToken) {
            return [
                'access_token' => $this->accessToken,
                'token_type' => $this->tokenType,
                'user' => $userData,
            ];
        }

        return [
            'user' => $userData,
        ];
    }

    /**
     * Get JSON response structure
     */
    public function toJsonResponse(int $statusCode = 201): array
    {
        return [
            'data' => $this->toArray(),
            'status' => $statusCode,
        ];
    }
}
