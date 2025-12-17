<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Password Value Object
 * 
 * Encapsulates password validation and hashing logic
 */
class Password
{
    private string $hashedValue;
    private const MIN_LENGTH = 8;

    private function __construct(string $hashedPassword)
    {
        $this->hashedValue = $hashedPassword;
    }

    /**
     * Create from plain text password
     */
    public static function fromPlainText(string $plainPassword): self
    {
        self::validatePlainPassword($plainPassword);
        return new self(self::hash($plainPassword));
    }

    /**
     * Create from already hashed password
     */
    public static function fromHash(string $hashedPassword): self
    {
        return new self($hashedPassword);
    }

    private static function validatePlainPassword(string $password): void
    {
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        if (strlen($password) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Password must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        // Additional validation: must contain at least one letter and one number
        if (!preg_match('/[A-Za-z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one letter');
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one number');
        }
    }

    private static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hashedValue);
    }

    public function getHashedValue(): string
    {
        return $this->hashedValue;
    }

    public function __toString(): string
    {
        return $this->hashedValue;
    }
}
