<?php

namespace App\Application\DTOs;

/**
 * Register User Data Transfer Object
 * 
 * Transfers registration data between layers
 */
class RegisterUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $passwordConfirmation
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            passwordConfirmation: $data['password_confirmation'] ?? ''
        );
    }

    public function passwordsMatch(): bool
    {
        return $this->password === $this->passwordConfirmation;
    }
}
