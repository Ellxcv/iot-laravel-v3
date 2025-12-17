<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\NotificationSetting;
use App\Domain\Entities\NotificationLog;

interface NotificationRepositoryInterface
{
    public function getSettingsByUserId(int $userId): ?NotificationSetting;
    
    public function saveSettings(NotificationSetting $settings): NotificationSetting;
    
    public function saveLog(NotificationLog $log): NotificationLog;
    
    public function getLogsByUserId(int $userId, int $limit = 50): array;
}
