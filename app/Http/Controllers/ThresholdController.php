<?php

namespace App\Http\Controllers;

use App\Models\IoTDevice;
use App\Models\SensorThreshold;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThresholdController extends Controller
{
    /**
     * Get thresholds for a device
     */
    public function index(int $deviceId): JsonResponse
    {
        $device = IoTDevice::where('id', $deviceId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $thresholds = SensorThreshold::where('device_id', $deviceId)->get();

        return response()->json([
            'success' => true,
            'device' => $device,
            'thresholds' => $thresholds,
        ]);
    }

    /**
     * Update thresholds for a device
     */
    public function update(Request $request, int $deviceId): JsonResponse
    {
        $device = IoTDevice::where('id', $deviceId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $validated = $request->validate([
            'thresholds' => 'required|array',
            'thresholds.*.sensor_type' => 'required|string|in:temperature,humidity,air_quality,water_level,weight',
            'thresholds.*.min_value' => 'nullable|numeric',
            'thresholds.*.max_value' => 'nullable|numeric',
            'thresholds.*.enabled' => 'required|boolean',
            'thresholds.*.cooldown_minutes' => 'required|integer|min:1|max:1440',
        ]);

        foreach ($validated['thresholds'] as $thresholdData) {
            SensorThreshold::updateOrCreate(
                [
                    'device_id' => $deviceId,
                    'sensor_type' => $thresholdData['sensor_type'],
                ],
                [
                    'min_value' => $thresholdData['min_value'],
                    'max_value' => $thresholdData['max_value'],
                    'enabled' => $thresholdData['enabled'],
                    'cooldown_minutes' => $thresholdData['cooldown_minutes'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Thresholds updated successfully',
        ]);
    }
}
