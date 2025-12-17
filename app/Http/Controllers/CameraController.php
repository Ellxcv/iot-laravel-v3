<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Camera\GetCameraStreamUseCase;
use App\Application\UseCases\Camera\PublishCameraCommandUseCase;
use App\Application\UseCases\Camera\StoreCameraImageUseCase;
use App\Application\UseCases\Camera\GetCameraImagesUseCase;
use App\Application\DTOs\CameraCommandDTO;
use App\Application\DTOs\CameraImageDTO;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CameraController extends Controller
{
    public function __construct(
        private GetCameraStreamUseCase $getCameraStreamUseCase,
        private PublishCameraCommandUseCase $publishCameraCommandUseCase,
        private StoreCameraImageUseCase $storeCameraImageUseCase,
        private GetCameraImagesUseCase $getCameraImagesUseCase,
    ) {}

    /**
     * Show camera live stream page
     */
    public function live(Request $request): View
    {
        $cameras = $this->getCameraStreamUseCase->getAllCameras();
        
        $selectedDeviceId = $request->query('device_id') ?? ($cameras[0]->deviceId ?? null);
        
        $selectedCamera = null;
        if ($selectedDeviceId) {
            try {
                $selectedCamera = $this->getCameraStreamUseCase->execute($selectedDeviceId);
            } catch (InvalidArgumentException $e) {
                // Camera not found, will show error in view
            }
        }
        
        return view('camera.live', compact('cameras', 'selectedCamera'));
    }

    /**
     * Capture single image
     */
    public function capture(Request $request, string $deviceId): JsonResponse
    {
        $command = CameraCommandDTO::capture($deviceId);
        $success = $this->publishCameraCommandUseCase->execute($command);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Capture command sent' : 'Failed to send command',
        ]);
    }

    /**
     * Start streaming (auto-capture every 5s)
     */
    public function startStream(Request $request, string $deviceId): JsonResponse
    {
        $command = CameraCommandDTO::streamStart($deviceId);
        $success = $this->publishCameraCommandUseCase->execute($command);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Streaming started' : 'Failed to start streaming',
        ]);
    }

    /**
     * Stop streaming
     */
    public function stopStream(Request $request, string $deviceId): JsonResponse
    {
        $command = CameraCommandDTO::streamStop($deviceId);
        $success = $this->publishCameraCommandUseCase->execute($command);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Streaming stopped' : 'Failed to stop streaming',
        ]);
    }

    /**
     * Control flash LED
     */
    public function flashControl(Request $request, string $deviceId): JsonResponse
    {
        $request->validate([
            'state' => 'required|in:on,off',
        ]);

        $command = $request->state === 'on' 
            ? CameraCommandDTO::flashOn($deviceId)
            : CameraCommandDTO::flashOff($deviceId);
            
        $success = $this->publishCameraCommandUseCase->execute($command);

        return response()->json([
            'success' => $success,
            'message' => $success ? "Flash {$request->state}" : 'Failed to control flash',
        ]);
    }

    /**
     * Set image quality
     */
    public function setQuality(Request $request, string $deviceId): JsonResponse
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:63',
        ]);

        $command = CameraCommandDTO::setQuality($deviceId, (int) $request->quality);
        $success = $this->publishCameraCommandUseCase->execute($command);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Quality updated' : 'Failed to set quality',
        ]);
    }

    /**
     * Set resolution
     */
    public function setResolution(Request $request, string $deviceId): JsonResponse
    {
        $request->validate([
            'resolution' => 'required|in:UXGA,SXGA,XGA,SVGA,VGA,QVGA',
        ]);

        $command = CameraCommandDTO::setResolution($deviceId, $request->resolution);
        $success = $this->publishCameraCommandUseCase->execute($command);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Resolution updated' : 'Failed to set resolution',
        ]);
    }

    /**
     * Upload image from ESP32 CAM (HTTP POST)
     */
    public function uploadImage(Request $request): JsonResponse
    {
        // Validate API key
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== env('ESP32_API_KEY', 'supersecretkey123')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        $deviceId = $request->header('X-Device-ID');
        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID required',
            ], 400);
        }

        // Get image data from request body
        $imageData = $request->getContent();

        if (empty($imageData)) {
            return response()->json([
                'success' => false,
                'message' => 'No image data received',
            ], 400);
        }

        try {
            // Generate filename
            $filename = $deviceId . '_' . now()->format('YmdHis') . '.jpg';
            $path = 'camera/' . $deviceId . '/' . now()->format('Y/m/d') . '/' . $filename;

            // Store image
            Storage::put($path, $imageData);

            // Get image dimensions (optional)
            $width = null;
            $height = null;
            try {
                $tempPath = storage_path('app/' . $path);
                if (file_exists($tempPath)) {
                    list($width, $height) = getimagesize($tempPath);
                }
            } catch (\Exception $e) {
                // Ignore dimension errors
            }

            // Create DTO and store via use case
            $dto = CameraImageDTO::fromArray([
                'device_id' => $deviceId,
                'filename' => $filename,
                'path' => $path,
                'size' => strlen($imageData),
                'width' => $width,
                'height' => $height,
                'format' => 'jpeg',
                'captured_at' => now()->toDateTimeString(),
            ]);

            $this->storeCameraImageUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'size' => strlen($imageData),
                    'url' => asset('storage/' . $path),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to upload camera image', [
                'error' => $e->getMessage(),
                'device_id' => $deviceId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get camera images gallery
     */
    public function gallery(Request $request, string $deviceId): View
    {
        $images = $this->getCameraImagesUseCase->execute($deviceId);

        return view('camera.gallery', compact('images', 'deviceId'));
    }

    /**
     * Get latest image
     */
    public function latest(string $deviceId): JsonResponse
    {
        $image = $this->getCameraImagesUseCase->getLatest($deviceId);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'No images found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'image' => $image,
        ]);
    }

    /**
     * Get device status (for real-time polling)
     */
    public function getDeviceStatus(string $deviceId): JsonResponse
    {
        try {
            $camera = $this->getCameraStreamUseCase->execute($deviceId);

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $camera->status,
                    'fps' => $camera->fps ?? 0,
                    'ip' => $camera->ip,
                    'lastSeen' => $camera->lastSeen?->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }
    }
}
