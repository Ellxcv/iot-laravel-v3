<?php

namespace App\Application\DTOs;

/**
 * Login User Data Transfer Object
 * 
 * Transfers login credentials between layers
 */
class LoginUserDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $remember = false
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            remember: (bool)($data['remember'] ?? false)
        );
    }
}
