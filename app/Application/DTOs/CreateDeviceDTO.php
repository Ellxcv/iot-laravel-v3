<?php

namespace App\Application\DTOs;

use Illuminate\Http\Request;

class CreateDeviceDTO
{
    public function __construct(
        public readonly string $deviceId,
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $description = null,
        public readonly ?int $userId = null,
        public readonly string $status = 'active'
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            deviceId: $request->input('device_id'),
            name: $request->input('name'),
            type: $request->input('type'),
            description: $request->input('description'),
            userId: $request->input('user_id'),
            status: $request->input('status', 'active')
        );
    }

    public function toArray(): array
    {
        return [
            'device_id' => $this->deviceId,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'user_id' => $this->userId,
            'status' => $this->status,
            'is_active' => $this->status === 'active',
        ];
    }
}
