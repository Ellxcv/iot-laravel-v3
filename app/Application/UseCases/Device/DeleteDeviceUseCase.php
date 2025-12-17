<?php

namespace App\Application\UseCases\Device;

use App\Domain\Repositories\DeviceRepositoryInterface;
use Exception;

class DeleteDeviceUseCase
{
    public function __construct(
        private DeviceRepositoryInterface $deviceRepository
    ) {}

    /**
     * Execute the use case
     * 
     * @throws Exception
     */
    public function execute(int $id): bool
    {
        // Check if device exists
        $device = $this->deviceRepository->findById($id);
        
        if (!$device) {
            throw new Exception('Device not found');
        }

        // Delete device
        return $this->deviceRepository->delete($id);
    }
}
