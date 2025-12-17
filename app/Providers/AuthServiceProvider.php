<?php

namespace App\Providers;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Authentication Service Provider
 * 
 * Binds interfaces to implementations for dependency injection
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind UserRepositoryInterface to EloquentUserRepository
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
