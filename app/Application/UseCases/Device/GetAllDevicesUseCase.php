<?php

namespace App\Application\UseCases\Device;

use App\Domain\Repositories\DeviceRepositoryInterface;

class GetAllDevicesUseCase
{
    public function __construct(
        private DeviceRepositoryInterface $deviceRepository
    ) {}

    /**
     * Execute the use case
     * 
     * @param int|null $userId Optional user ID to filter devices
     * @return array<\App\Domain\Entities\Device\DeviceEntity>
     */
    public function execute(?int $userId = null): array
    {
        if ($userId !== null) {
            return $this->deviceRepository->getAllForUser($userId);
        }
        
        return $this->deviceRepository->getAll();
    }
}
