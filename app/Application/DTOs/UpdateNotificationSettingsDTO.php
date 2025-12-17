<?php

namespace App\Application\DTOs;

class UpdateNotificationSettingsDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $botToken,
        public readonly ?string $chatId,
        public readonly bool $enabled,
        public readonly ?string $fcmDeviceToken = null,
        public readonly bool $firebaseEnabled = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            botToken: $data['bot_token'] ?? null,
            chatId: $data['chat_id'] ?? null,
            enabled: $data['enabled'] ?? false,
            fcmDeviceToken: $data['fcm_device_token'] ?? null,
            firebaseEnabled: $data['firebase_enabled'] ?? false,
        );
    }
}
