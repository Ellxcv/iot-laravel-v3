<?php

namespace App\Application\DTOs;

class SendNotificationDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $type,
        public readonly string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            type: $data['type'],
            message: $data['message'],
        );
    }
}
