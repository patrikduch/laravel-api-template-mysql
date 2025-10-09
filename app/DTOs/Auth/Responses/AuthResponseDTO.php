<?php

namespace App\DTOs\Auth\Responses;

readonly class AuthResponseDTO
{
    public function __construct(
        public string $access_token,
        public string $token_type = 'Bearer',
    ) {
    }

    public static function fromToken(string $token): self
    {
        return new self(
            access_token: $token,
            token_type: 'Bearer',
        );
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->access_token,
            'token_type' => $this->token_type,
        ];
    }
}
