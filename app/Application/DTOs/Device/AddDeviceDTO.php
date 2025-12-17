<?php

namespace App\Application\DTOs\Device;

class AddDeviceDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $deviceId,
        public readonly string $type,
        public readonly bool $isActive = true
    ) {}

    /**
     * Create from array (typically from request)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            deviceId: $data['device_id'] ?? '',
            type: $data['type'] ?? 'sensor',
            isActive: $data['is_active'] ?? true
        );
    }

    /**
     * Validate the DTO data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors['name'] = 'Device name is required';
        }

        if (empty($this->deviceId)) {
            $errors['device_id'] = 'Device ID is required';
        }

        if (!in_array($this->type, ['sensor', 'camera', 'controller'])) {
            $errors['type'] = 'Invalid device type';
        }

        return $errors;
    }

    /**
     * Check if DTO is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }
}
