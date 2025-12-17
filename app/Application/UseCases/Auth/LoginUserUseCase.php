<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\LoginUserDTO;
use App\Domain\Entities\UserEntity;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\ValueObjects\Email;
use InvalidArgumentException;

/**
 * Login User Use Case
 * 
 * Handles the business logic for user authentication
 */
class LoginUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Execute the login use case
     * 
     * @throws InvalidArgumentException
     */
    public function execute(LoginUserDTO $dto): UserEntity
    {
        // Create email value object (validates format)
        $email = new Email($dto->email);

        // Find user by email
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new InvalidArgumentException('Invalid credentials');
        }

        // Verify password
        if (!$user->verifyPassword($dto->password)) {
            throw new InvalidArgumentException('Invalid credentials');
        }

        return $user;
    }
}
