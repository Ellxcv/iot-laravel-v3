<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\RegisterUserDTO;
use App\Domain\Entities\UserEntity;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use InvalidArgumentException;

/**
 * Register User Use Case
 * 
 * Handles the business logic for user registration
 */
class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Execute the registration use case
     * 
     * @throws InvalidArgumentException
     */
    public function execute(RegisterUserDTO $dto): UserEntity
    {
        // Create value objects (this will validate the data)
        $email = new Email($dto->email);
        $password = Password::fromPlainText($dto->password);

        // Check if email already exists
        if ($this->userRepository->emailExists($email)) {
            throw new InvalidArgumentException('Email already registered');
        }

        // Create user entity
        $user = new UserEntity(
            name: $dto->name,
            email: $email,
            password: $password
        );

        // Save to repository
        return $this->userRepository->save($user);
    }
}
