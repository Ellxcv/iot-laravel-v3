<?php

namespace App\Domain\Entities;

class NotificationLog
{
    public function __construct(
        public int $userId,
        public string $type,
        public string $message,
        public string $status = 'sent',
        public ?\DateTime $sentAt = null,
        public ?string $errorMessage = null,
        public ?int $id = null,
    ) {
        $this->validateUserId($userId);
        $this->validateType($type);
        $this->validateStatus($status);
    }

    private function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User ID must be positive');
        }
    }

    private function validateType(string $type): void
    {
        $validTypes = ['sensor_alert', 'device_status', 'manual_test'];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException('Invalid notification type');
        }
    }

    private function validateStatus(string $status): void
    {
        if (!in_array($status, ['sent', 'failed'])) {
            throw new \InvalidArgumentException('Status must be sent or failed');
        }
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
