<?php

namespace App\Providers;

use App\Domain\Repositories\CameraRepositoryInterface;
use App\Infrastructure\Repositories\EloquentCameraRepository;
use Illuminate\Support\ServiceProvider;

class CameraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CameraRepositoryInterface::class, EloquentCameraRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
