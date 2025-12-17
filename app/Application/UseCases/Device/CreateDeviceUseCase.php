<?php

namespace App\Application\UseCases\Device;

use App\Application\DTOs\CreateDeviceDTO;
use App\Models\IoTDevice;
use Illuminate\Support\Facades\DB;

class CreateDeviceUseCase
{
    /**
     * Execute the use case to create a new device
     */
    public function execute(CreateDeviceDTO $dto): IoTDevice
    {
        return DB::transaction(function () use ($dto) {
            $device = IoTDevice::create($dto->toArray());
            
            // Log device creation
            \Log::info('Device created', [
                'device_id' => $device->device_id,
                'name' => $device->name,
                'created_by' => auth()->id()
            ]);

            return $device;
        });
    }
}
