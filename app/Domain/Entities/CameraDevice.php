<?php

namespace App\Domain\Entities;

class CameraDevice
{
    public function __construct(
        public string $deviceId,
        public string $name,
        public string $streamUrl,
        public string $type = 'esp32cam',
        public string $status = 'offline',
        public ?string $resolution = null,
        public ?int $fps = null,
        public ?string $description = null,
        public ?int $id = null,
        public ?\DateTime $lastSeen = null,
    ) {
        $this->validateDeviceId($deviceId);
        $this->validateStreamUrl($streamUrl);
    }

    private function validateDeviceId(string $deviceId): void
    {
        if (empty($deviceId)) {
            throw new \InvalidArgumentException('Device ID cannot be empty');
        }
    }

    private function validateStreamUrl(string $streamUrl): void
    {
        if (empty($streamUrl) || !filter_var($streamUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid stream URL');
        }
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
