<?php

namespace App\Application\UseCases\Camera;

use App\Models\IoTDevice;
use App\Models\CameraImage;
use InvalidArgumentException;

class GetCameraStreamUseCase
{
    public function execute(string $deviceId): object
    {
        $device = IoTDevice::where('device_id', $deviceId)->first();

        if (!$device) {
            throw new InvalidArgumentException("Camera device not found: {$deviceId}");
        }

        $latestImage = CameraImage::where('device_id', $deviceId)
            ->latest('captured_at')
            ->first();

        // Build stream URL from ESP32 CAM IP
        $streamUrl = $device->ip 
            ? "http://{$device->ip}/stream" 
            : '';

        return (object) [
            'deviceId' => $device->device_id,
            'name' => $device->name ?? $device->device_id,
            'type' => $device->type ?? 'camera',
            'status' => $device->status ?? 'offline',
            'ip' => $device->ip,
            'fps' => $device->fps ?? 0,
            'resolution' => null, // ESP32 CAM resolution can be added later
            'description' => $device->description,
            'streamUrl' => $streamUrl,
            'lastSeen' => $device->last_seen,
            'latestImageUrl' => $latestImage?->url ?? null,
            'lastImageAt' => $device->last_image_at,
        ];
    }

    /**
     * Check if camera is online (helper method for views)
     */
    private function isOnline(object $camera): bool
    {
        if (!$camera->lastSeen) {
            return false;
        }
        
        // Device is online if last_seen is within 5 minutes
        return $camera->lastSeen->greaterThan(now()->subMinutes(5));
    }

    public function getAllCameras(): array
    {
        $cameras = IoTDevice::where('type', 'camera')
            ->orWhere('device_id', 'like', '%cam%')
            ->get();

        return $cameras->map(function ($device) {
            return (object) [
                'deviceId' => $device->device_id,
                'name' => $device->name ?? $device->device_id,
                'status' => $device->status ?? 'offline',
            ];
        })->toArray();
    }
}
