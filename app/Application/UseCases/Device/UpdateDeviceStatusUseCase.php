<?php

namespace App\Application\UseCases\Device;

use App\Application\DTOs\Device\UpdateDeviceStatusDTO;
use App\Domain\Repositories\DeviceRepositoryInterface;
use Exception;

class UpdateDeviceStatusUseCase
{
    public function __construct(
        private DeviceRepositoryInterface $deviceRepository
    ) {}

    /**
     * Execute the use case
     * 
     * @throws Exception
     */
    public function execute(UpdateDeviceStatusDTO $dto): bool
    {
        // Check if device exists
        $device = $this->deviceRepository->findById($dto->id);
        
        if (!$device) {
            throw new Exception('Device not found');
        }

        // Update status
        return $this->deviceRepository->updateStatus($dto->id, $dto->isActive);
    }
}
