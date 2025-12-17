<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\UserEntity;
use App\Domain\ValueObjects\Email;

/**
 * User Repository Interface
 * 
 * Defines the contract for user data persistence
 * Framework-agnostic interface in the domain layer
 */
interface UserRepositoryInterface
{
    /**
     * Save a user entity to storage
     */
    public function save(UserEntity $user): UserEntity;

    /**
     * Find a user by their email address
     */
    public function findByEmail(Email $email): ?UserEntity;

    /**
     * Find a user by their ID
     */
    public function findById(int $id): ?UserEntity;

    /**
     * Check if an email already exists in the system
     */
    public function emailExists(Email $email): bool;

    /**
     * Delete a user from storage
     */
    public function delete(UserEntity $user): bool;
}
