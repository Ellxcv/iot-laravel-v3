<?php

namespace App\Application\UseCases\Notification;

use App\Application\DTOs\SendNotificationDTO;
use App\Domain\Entities\NotificationLog;
use App\Domain\Repositories\NotificationRepositoryInterface;
use App\Infrastructure\Services\TelegramNotificationService;
use App\Infrastructure\Services\FirebaseNotificationService;
use InvalidArgumentException;

class SendNotificationUseCase
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private TelegramNotificationService $telegramService,
        private FirebaseNotificationService $firebaseService
    ) {}

    public function execute(SendNotificationDTO $dto): NotificationLog
    {
        // Get notification settings for user
        $settings = $this->notificationRepository->getSettingsByUserId($dto->userId);

        if (!$settings) {
            throw new InvalidArgumentException('Notification settings not found for user');
        }

        $sentChannels = [];
        $errors = [];
        $sentAt = new \DateTime();

        // Send via Telegram
        if ($settings->canSendNotifications()) {
            try {
                $this->telegramService->send(
                    $settings->botToken,
                    $settings->chatId,
                    $dto->message
                );
                $sentChannels[] = 'telegram';
            } catch (\Exception $e) {
                $errors[] = 'Telegram: ' . $e->getMessage();
            }
        }

        // Send via Firebase (to ALL user devices)
        if ($settings->canSendFirebaseNotifications()) {
            try {
                // Get all FCM tokens for this user
                $userTokens = \App\Models\UserFcmToken::where('user_id', $dto->userId)->get();
                
                if ($userTokens->isNotEmpty()) {
                    $tokens = $userTokens->pluck('fcm_token')->toArray();
                    
                    // Send to all devices
                    $results = $this->firebaseService->sendToMultipleDevices(
                        $tokens,
                        'IoT Alert',
                        $dto->message
                    );
                    
                    // Remove invalid tokens
                    if (!empty($results['invalid_tokens'])) {
                        \App\Models\UserFcmToken::whereIn('fcm_token', $results['invalid_tokens'])
                            ->delete();
                    }
                    
                    if ($results['success'] > 0) {
                        $sentChannels[] = "firebase ({$results['success']} devices)";
                    }
                    
                    if ($results['failed'] > 0) {
                        $errors[] = "Firebase: {$results['failed']} devices failed";
                    }
                } else {
                    $errors[] = 'Firebase: No registered devices found';
                }
            } catch (\Exception $e) {
                $errors[] = 'Firebase: ' . $e->getMessage();
            }
        }

        // Determine overall status
        if (count($sentChannels) === 0) {
            if (count($errors) === 0) {
                throw new InvalidArgumentException('No notification channels are enabled or configured');
            }
            $status = 'failed';
            $errorMessage = implode('; ', $errors);
        } else {
            $status = 'sent';
            $errorMessage = count($errors) > 0 ? 'Partial success. ' . implode('; ', $errors) : null;
        }

        // Create log entry
        $log = new NotificationLog(
            userId: $dto->userId,
            type: $dto->type,
            message: $dto->message . ' [Sent via: ' . implode(', ', $sentChannels) . ']',
            status: $status,
            sentAt: $sentAt,
            errorMessage: $errorMessage,
        );

        return $this->notificationRepository->saveLog($log);
    }
}
