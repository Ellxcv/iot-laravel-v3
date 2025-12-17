<?php

namespace App\Providers;

use App\Domain\Repositories\DeviceRepositoryInterface;
use App\Infrastructure\Repositories\EloquentDeviceRepository;
use Illuminate\Support\ServiceProvider;

class DeviceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            DeviceRepositoryInterface::class,
            EloquentDeviceRepository::class
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
