<?php

namespace App\Application\UseCases\Device;

use App\Models\IoTDevice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListDevicesUseCase
{
    /**
     * Execute the use case to list all devices with pagination
     */
    public function execute(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = IoTDevice::query()->with(['user:id,name,email']);

        // Apply filters if provided
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('device_id', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
