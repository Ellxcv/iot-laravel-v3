<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Email Value Object
 * 
 * Encapsulates email validation logic and ensures email is always valid
 */
class Email
{
    private string $value;

    public function __construct(string $email)
    {
        $this->validate($email);
        $this->value = strtolower(trim($email));
    }

    private function validate(string $email): void
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        // Additional validation: check for common typos in domains
        $domain = substr(strrchr($email, "@"), 1);
        if (strlen($domain) < 3) {
            throw new InvalidArgumentException('Invalid email domain');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
