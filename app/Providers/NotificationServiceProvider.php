<?php

namespace App\Providers;

use App\Domain\Repositories\NotificationRepositoryInterface;
use App\Domain\Services\NotificationServiceInterface;
use App\Infrastructure\Repositories\EloquentNotificationRepository;
use App\Infrastructure\Services\TelegramNotificationService;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationRepositoryInterface::class, EloquentNotificationRepository::class);
        $this->app->bind(NotificationServiceInterface::class, TelegramNotificationService::class);
    }

    public function boot(): void
    {
        //
    }
}
