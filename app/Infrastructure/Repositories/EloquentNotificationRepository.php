<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\NotificationSetting as NotificationSettingEntity;
use App\Domain\Entities\NotificationLog as NotificationLogEntity;
use App\Domain\Repositories\NotificationRepositoryInterface;
use App\Models\NotificationSetting as NotificationSettingModel;
use App\Models\NotificationLog as NotificationLogModel;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function getSettingsByUserId(int $userId): ?NotificationSettingEntity
    {
        $model = NotificationSettingModel::where('user_id', $userId)->first();
        
        if (!$model) {
            return null;
        }
        
        return $this->settingsModelToEntity($model);
    }

    public function saveSettings(NotificationSettingEntity $settings): NotificationSettingEntity
    {
        $model = $settings->getId()
            ? NotificationSettingModel::find($settings->getId())
            : NotificationSettingModel::where('user_id', $settings->userId)->first();

        if (!$model) {
            $model = new NotificationSettingModel();
        }

        $model->user_id = $settings->userId;
        $model->bot_token = $settings->botToken;
        $model->chat_id = $settings->chatId;
        $model->enabled = $settings->enabled;
        $model->fcm_device_token = $settings->fcmDeviceToken;
        $model->firebase_enabled = $settings->firebaseEnabled;
        $model->save();

        return $this->settingsModelToEntity($model);
    }

    public function saveLog(NotificationLogEntity $log): NotificationLogEntity
    {
        $model = new NotificationLogModel();
        $model->user_id = $log->userId;
        $model->type = $log->type;
        $model->message = $log->message;
        $model->status = $log->status;
        $model->sent_at = $log->sentAt;
        $model->error_message = $log->errorMessage;
        $model->save();

        return $this->logModelToEntity($model);
    }

    public function getLogsByUserId(int $userId, int $limit = 50): array
    {
        $models = NotificationLogModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(function ($model) {
            return $this->logModelToEntity($model);
        })->toArray();
    }

    private function settingsModelToEntity(NotificationSettingModel $model): NotificationSettingEntity
    {
        return new NotificationSettingEntity(
            userId: $model->user_id,
            botToken: $model->bot_token,
            chatId: $model->chat_id,
            enabled: $model->enabled,
            fcmDeviceToken: $model->fcm_device_token,
            firebaseEnabled: $model->firebase_enabled,
            id: $model->id,
        );
    }

    private function logModelToEntity(NotificationLogModel $model): NotificationLogEntity
    {
        return new NotificationLogEntity(
            userId: $model->user_id,
            type: $model->type,
            message: $model->message,
            status: $model->status,
            sentAt: $model->sent_at,
            errorMessage: $model->error_message,
            id: $model->id,
        );
    }
}
