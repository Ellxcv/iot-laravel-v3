<?php

namespace App\Providers;

use App\Domain\Repositories\IoTRepositoryInterface;
use App\Infrastructure\Repositories\EloquentIoTRepository;
use Illuminate\Support\ServiceProvider;

class IoTServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IoTRepositoryInterface::class, EloquentIoTRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
