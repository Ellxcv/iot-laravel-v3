<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\UserEntity;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use App\Models\User;

/**
 * Eloquent User Repository Implementation
 * 
 * Laravel-specific implementation of UserRepositoryInterface
 * Bridges between domain entities and Eloquent models
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(UserEntity $userEntity): UserEntity
    {
        // Convert entity to Eloquent model
        $user = new User();
        $user->name = $userEntity->getName();
        $user->email = $userEntity->getEmail()->getValue();
        $user->password = $userEntity->getPassword()->getHashedValue();
        $user->save();

        // Convert back to entity with ID
        return $this->toEntity($user);
    }

    public function findByEmail(Email $email): ?UserEntity
    {
        $user = User::where('email', $email->getValue())->first();
        
        return $user ? $this->toEntity($user) : null;
    }

    public function findById(int $id): ?UserEntity
    {
        $user = User::find($id);
        
        return $user ? $this->toEntity($user) : null;
    }

    public function emailExists(Email $email): bool
    {
        return User::where('email', $email->getValue())->exists();
    }

    public function delete(UserEntity $userEntity): bool
    {
        if ($userEntity->getId() === null) {
            return false;
        }

        $user = User::find($userEntity->getId());
        
        return $user ? $user->delete() : false;
    }

    /**
     * Convert Eloquent model to domain entity
     */
    private function toEntity(User $user): UserEntity
    {
        return new UserEntity(
            name: $user->name,
            email: new Email($user->email),
            password: Password::fromHash($user->password),
            id: $user->id,
            emailVerifiedAt: $user->email_verified_at,
            createdAt: $user->created_at,
            updatedAt: $user->updated_at
        );
    }
}
