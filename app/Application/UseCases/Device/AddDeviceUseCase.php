<?php

namespace App\Application\UseCases\Device;

use App\Application\DTOs\Device\AddDeviceDTO;
use App\Domain\Entities\Device\DeviceEntity;
use App\Domain\Repositories\DeviceRepositoryInterface;
use Exception;

class AddDeviceUseCase
{
    public function __construct(
        private DeviceRepositoryInterface $deviceRepository
    ) {}

    /**
     * Execute the use case
     * 
     * @throws Exception
     */
    public function execute(AddDeviceDTO $dto): DeviceEntity
    {
        // Validate DTO
        if (!$dto->isValid()) {
            $errors = $dto->validate();
            throw new Exception('Validation failed: ' . implode(', ', $errors));
        }

        // Check if device_id already exists
        if ($this->deviceRepository->deviceIdExists($dto->deviceId)) {
            throw new Exception('Device ID already exists');
        }

        // Create device entity
        $device = new DeviceEntity(
            name: $dto->name,
            deviceId: $dto->deviceId,
            type: $dto->type,
            isActive: $dto->isActive,
            status: 'offline' // New devices start as offline
        );

        // Save to repository
        return $this->deviceRepository->save($device);
    }
}
