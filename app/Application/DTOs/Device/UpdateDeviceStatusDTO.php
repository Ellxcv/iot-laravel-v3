<?php

namespace App\Application\DTOs\Device;

class UpdateDeviceStatusDTO
{
    public function __construct(
        public readonly int $id,
        public readonly bool $isActive
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? false)
        );
    }
}
