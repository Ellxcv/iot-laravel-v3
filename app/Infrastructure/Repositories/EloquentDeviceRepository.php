<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Device\DeviceEntity;
use App\Domain\Repositories\DeviceRepositoryInterface;
use App\Models\IoTDevice;
use DateTime;

class EloquentDeviceRepository implements DeviceRepositoryInterface
{
    /**
     * Get all devices
     */
    public function getAll(): array
    {
        $models = IoTDevice::orderBy('created_at', 'desc')->get();
        
        return $models->map(fn($model) => $this->modelToEntity($model))->all();
    }

    /**
     * Find device by ID
     */
    public function findById(int $id): ?DeviceEntity
    {
        $model = IoTDevice::find($id);
        
        return $model ? $this->modelToEntity($model) : null;
    }

    /**
     * Find device by device_id
     */
    public function findByDeviceId(string $deviceId): ?DeviceEntity
    {
        $model = IoTDevice::where('device_id', $deviceId)->first();
        
        return $model ? $this->modelToEntity($model) : null;
    }

    /**
     * Save device (create or update)
     */
    public function save(DeviceEntity $device): DeviceEntity
    {
        if ($device->id) {
            // Update existing
            $model = IoTDevice::findOrFail($device->id);
            $model->update($this->entityToArray($device));
        } else {
            // Create new
            $model = IoTDevice::create($this->entityToArray($device));
        }

        return $this->modelToEntity($model->fresh());
    }

    /**
     * Delete device by ID
     */
    public function delete(int $id): bool
    {
        $model = IoTDevice::find($id);
        
        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    /**
     * Update device active status
     */
    public function updateStatus(int $id, bool $isActive): bool
    {
        return IoTDevice::where('id', $id)
            ->update(['is_active' => $isActive]) > 0;
    }

    /**
     * Get all active devices
     */
    public function getActiveDevices(): array
    {
        $models = IoTDevice::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $models->map(fn($model) => $this->modelToEntity($model))->all();
    }

    /**
     * Get all devices for a specific user
     */
    public function getAllForUser(int $userId): array
    {
        $models = IoTDevice::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $models->map(fn($model) => $this->modelToEntity($model))->all();
    }

    /**
     * Check if device_id already exists
     */
    public function deviceIdExists(string $deviceId, ?int $excludeId = null): bool
    {
        $query = IoTDevice::where('device_id', $deviceId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Convert Eloquent model to Domain Entity
     */
    private function modelToEntity(IoTDevice $model): DeviceEntity
    {
        return new DeviceEntity(
            id: $model->id,
            name: $model->name,
            deviceId: $model->device_id,
            type: $model->type ?? 'sensor',
            status: $model->status,
            isActive: $model->is_active,
            lastSeen: $model->last_seen ? new DateTime($model->last_seen->toDateTimeString()) : null,
            createdAt: $model->created_at ? new DateTime($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTime($model->updated_at->toDateTimeString()) : null
        );
    }

    /**
     * Convert Domain Entity to array for Eloquent
     */
    private function entityToArray(DeviceEntity $entity): array
    {
        return [
            'name' => $entity->name,
            'device_id' => $entity->deviceId,
            'type' => $entity->type,
            'status' => $entity->status,
            'is_active' => $entity->isActive,
            'last_seen' => $entity->lastSeen?->format('Y-m-d H:i:s'),
        ];
    }
}
