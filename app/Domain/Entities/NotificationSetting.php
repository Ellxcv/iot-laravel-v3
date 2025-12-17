<?php

namespace App\Domain\Entities;

class NotificationSetting
{
    public function __construct(
        public int $userId,
        public ?string $botToken = null,
        public ?string $chatId = null,
        public bool $enabled = false,
        public ?string $fcmDeviceToken = null,
        public bool $firebaseEnabled = false,
        public ?int $id = null,
    ) {
        $this->validateUserId($userId);
    }

    private function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User ID must be positive');
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatId);
    }

    public function canSendNotifications(): bool
    {
        return $this->enabled && $this->isConfigured();
    }

    public function canSendFirebaseNotifications(): bool
    {
        return $this->firebaseEnabled && !empty($this->fcmDeviceToken);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
