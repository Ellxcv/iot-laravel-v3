<?php

namespace App\Application\UseCases\Camera;

use App\Application\DTOs\CameraImageDTO;
use App\Domain\Repositories\CameraImageRepositoryInterface;
use App\Models\IoTDevice;
use Illuminate\Support\Facades\Log;

class StoreCameraImageUseCase
{
    public function __construct(
        private CameraImageRepositoryInterface $cameraImageRepository
    ) {}

    public function execute(CameraImageDTO $dto): bool
    {
        try {
            // Store image in database
            $this->cameraImageRepository->create($dto);

            // Update device last_image_at timestamp
            IoTDevice::where('device_id', $dto->deviceId)->update([
                'last_image_at' => now(),
            ]);

            Log::info('Camera image stored', [
                'device_id' => $dto->deviceId,
                'filename' => $dto->filename,
                'size' => $dto->size,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to store camera image', [
                'device_id' => $dto->deviceId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
