<?php

namespace App\Application\UseCases\Notification;

use App\Domain\Entities\NotificationSetting;
use App\Domain\Repositories\NotificationRepositoryInterface;

class GetNotificationSettingsUseCase
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(int $userId): ?NotificationSetting
    {
        return $this->notificationRepository->getSettingsByUserId($userId);
    }
}
