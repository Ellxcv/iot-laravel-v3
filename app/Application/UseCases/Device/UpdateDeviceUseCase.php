<?php

namespace App\Application\UseCases\Device;

use App\Application\DTOs\UpdateDeviceDTO;
use App\Models\IoTDevice;
use Illuminate\Support\Facades\DB;

class UpdateDeviceUseCase
{
    /**
     * Execute the use case to update an existing device
     */
    public function execute(UpdateDeviceDTO $dto): IoTDevice
    {
        return DB::transaction(function () use ($dto) {
            $device = IoTDevice::findOrFail($dto->id);
            $device->update($dto->toArray());
            
            // Log device update
            \Log::info('Device updated', [
                'device_id' => $device->device_id,
                'name' => $device->name,
                'updated_by' => auth()->id()
            ]);

            return $device->fresh(['user']);
        });
    }
}
