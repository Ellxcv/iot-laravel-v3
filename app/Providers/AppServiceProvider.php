<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind CameraImageRepository
        $this->app->bind(
            \App\Domain\Repositories\CameraImageRepositoryInterface::class,
            \App\Infrastructure\Repositories\EloquentCameraImageRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS URLs when using ngrok or in production
        if (config('app.env') !== 'local' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
