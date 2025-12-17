<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Device\DeviceEntity;

interface DeviceRepositoryInterface
{
    /**
     * Get all devices
     * 
     * @return array<DeviceEntity>
     */
    public function getAll(): array;

    /**
     * Find device by ID
     */
    public function findById(int $id): ?DeviceEntity;

    /**
     * Find device by device_id (unique identifier)
     */
    public function findByDeviceId(string $deviceId): ?DeviceEntity;

    /**
     * Save device (create or update)
     */
    public function save(DeviceEntity $device): DeviceEntity;

    /**
     * Delete device by ID
     */
    public function delete(int $id): bool;

    /**
     * Update device active status
     */
    public function updateStatus(int $id, bool $isActive): bool;

    /**
     * Get all active devices
     * 
     * @return array<DeviceEntity>
     */
    public function getActiveDevices(): array;

    /**
     * Get all devices for a specific user
     * 
     * @param int $userId
     * @return array<DeviceEntity>
     */
    public function getAllForUser(int $userId): array;

    /**
     * Check if device_id already exists
     */
    public function deviceIdExists(string $deviceId, ?int $excludeId = null): bool;
}
