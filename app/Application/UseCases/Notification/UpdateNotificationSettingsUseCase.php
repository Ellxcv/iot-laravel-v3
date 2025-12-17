<?php

namespace App\Application\UseCases\Notification;

use App\Application\DTOs\UpdateNotificationSettingsDTO;
use App\Domain\Entities\NotificationSetting;
use App\Domain\Repositories\NotificationRepositoryInterface;

class UpdateNotificationSettingsUseCase
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(UpdateNotificationSettingsDTO $dto): NotificationSetting
    {
        // Auto-fill bot token from config if not provided
        $botToken = $dto->botToken ?? config('services.telegram.bot_token');
        
        $settings = new NotificationSetting(
            userId: $dto->userId,
            botToken: $botToken,
            chatId: $dto->chatId,
            enabled: $dto->enabled,
            fcmDeviceToken: $dto->fcmDeviceToken,
            firebaseEnabled: $dto->firebaseEnabled,
        );

        return $this->notificationRepository->saveSettings($settings);
    }
}
