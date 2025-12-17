<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use DateTimeInterface;

/**
 * User Entity
 * 
 * Pure domain entity representing a user in the system
 * No framework dependencies - pure business logic
 */
class UserEntity
{
    private ?int $id;
    private string $name;
    private Email $email;
    private Password $password;
    private ?DateTimeInterface $emailVerifiedAt;
    private ?DateTimeInterface $createdAt;
    private ?DateTimeInterface $updatedAt;

    public function __construct(
        string $name,
        Email $email,
        Password $password,
        ?int $id = null,
        ?DateTimeInterface $emailVerifiedAt = null,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null
    ) {
        $this->validateName($name);
        
        $this->id = $id;
        $this->name = trim($name);
        $this->email = $email;
        $this->password = $password;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        if (strlen($name) < 2) {
            throw new \InvalidArgumentException('Name must be at least 2 characters long');
        }

        if (strlen($name) > 255) {
            throw new \InvalidArgumentException('Name cannot exceed 255 characters');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getEmailVerifiedAt(): ?DateTimeInterface
    {
        return $this->emailVerifiedAt;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = new \DateTime();
    }

    public function updateName(string $newName): void
    {
        $this->validateName($newName);
        $this->name = trim($newName);
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return $this->password->verify($plainPassword);
    }
}
