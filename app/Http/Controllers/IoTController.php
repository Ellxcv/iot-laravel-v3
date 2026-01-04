<?php

namespace App\Http\Controllers;

use App\Models\IoTDevice;
use App\Models\SensorData;
use App\Models\DeviceCommand;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;  // ADDED: Direct MQTT facade

/**
 * IoT Controller
 * 
 * Handles IoT device monitoring and control
 */
class IoTController extends Controller
{
    /**
     * Show IoT status dashboard
     */
    public function status(Request $request): View
    {
        // Get device_id from request or use first device
        $requestedDeviceId = $request->query('device_id');
        
        // Check if user is admin
        $isAdmin = auth()->user()->isAdmin();
        
        // Get devices for selector based on role
        if ($isAdmin) {
            $devices = IoTDevice::orderBy('name')->get();
        } else {
            // Regular users only see their devices
            $devices = IoTDevice::where('user_id', auth()->id())
                ->orderBy('name')
                ->get();
        }
        
        // Get selected device
        if ($requestedDeviceId) {
            $query = IoTDevice::where('device_id', $requestedDeviceId);
            
            // If not admin, ensure user can only access their own devices
            if (!$isAdmin) {
                $query->where('user_id', auth()->id());
            }
            
            $device = $query->with(['latestSensorData', 'actuatorState'])->first();
            
            // If device not found or not authorized, redirect to first available device
            if (!$device) {
                $device = $devices->first();
            }
        } else {
            $device = $devices->first();
        }
        
        if (!$device) {
            // Create dummy device for demo - use unique ID per user
            $userId = auth()->id();
            $device = IoTDevice::create([
                'device_id' => 'DEMO_USER_' . $userId,
                'name' => 'Demo Device',
                'control_mode' => 'automatic',
                'status' => 'online',
                'last_seen' => now(),
                'user_id' => $userId,
            ]);
            
            // Create dummy sensor data
            SensorData::create([
                'device_id' => $device->id,
                'temperature' => 28.5,
                'humidity' => 65.0,
                'heat_index' => 30.2,
                'odor_index' => 120,
                'vpin' => 2.5,
                'vgas' => 3.2,
                'water_level' => 75.0,
                'water_zone' => 'normal',
                'soil_moisture' => 45.0,
                'soil_zone' => 'moist',
                'weight' => 1250.5,
            ]);
            
            // Create dummy actuator state
            $device->actuatorState()->create([
                'fan_duty_pct' => 50,
                'heater_duty_pct' => 30,
                'humid_duty_pct' => 40,
                'humidifier_on' => false,
                'heater_on' => false,
                'control_mode' => 'FUZZY',
            ]);
            
            $device->load(['latestSensorData', 'actuatorState']);
            
            // Reload devices list with proper user filtering
            if ($isAdmin) {
                $devices = IoTDevice::orderBy('name')->get();
            } else {
                $devices = IoTDevice::where('user_id', $userId)->orderBy('name')->get();
            }
        }
        
        return view('iot.status', compact('device', 'devices'));
    }

    /**
     * Get latest sensor data (AJAX endpoint for real-time updates)
     */
    public function getSensorData(Request $request): JsonResponse
    {
        $deviceId = $request->input('device_id');
        
        $device = IoTDevice::where('device_id', $deviceId)
            ->with(['latestSensorData', 'actuatorState'])
            ->first();
        
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }
        
        // Get latest sensor data
        $sensorData = $device->latestSensorData;
        
        // Return in format that frontend expects (nested sensors/actuators)
        return response()->json([
            'device' => [
                'id' => $device->device_id,
                'status' => $device->status,
                'control_mode' => $device->control_mode,
                'last_seen' => $device->last_seen?->toIso8601String(),
            ],
            'sensors' => $sensorData ? [
                'temperature' => $sensorData->temperature,
                'humidity' => $sensorData->humidity,
                'heat_index' => $sensorData->heat_index,
                'odor' => $sensorData->odor_index,
                'odor_index' => $sensorData->odor_index,
                'co2_ppm' => $sensorData->co2_ppm,
                'mq_vpin' => $sensorData->mq_vpin ?? $sensorData->vpin,
                'vpin' => $sensorData->vpin,
                'mq_vgas' => $sensorData->mq_vgas ?? $sensorData->vgas,
                'vgas' => $sensorData->vgas,
                'water_level' => $sensorData->water_level,
                'wl_zone' => $sensorData->wl_zone,
                'water_zone' => $sensorData->water_zone ?? $sensorData->wl_zone,
                'soil_pct' => $sensorData->soil_pct,
                'soil_moisture' => $sensorData->soil_moisture ?? $sensorData->soil_pct,
                'soil_zone' => $sensorData->soil_zone,
                'weight' => $sensorData->weight,
                'weight_grams' => $sensorData->weight,
            ] : null,
            'actuators' => $device->actuatorState ? [
                'fan_duty_pct' => $device->actuatorState->fan_duty_pct,
                'heater_duty_pct' => $device->actuatorState->heater_duty_pct,
                'humid_duty_pct' => $device->actuatorState->humid_duty_pct,
                'humidifier_on' => $device->actuatorState->humidifier_on,
                'heater_on' => $device->actuatorState->heater_on,
                'control_mode' => $device->actuatorState->control_mode,
            ] : null,
        ]);
    }

    /**
     * Get historical sensor data for charts
     */
    public function getHistoricalData(Request $request): JsonResponse
    {
        $deviceId = $request->input('device_id');
        $hours = $request->input('hours', 1);
        
        $device = IoTDevice::where('device_id', $deviceId)->first();
        
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }
        
        $data = SensorData::where('device_id', $device->id)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at')
            ->get(['created_at', 'temperature', 'humidity', 'water_level', 'soil_moisture', 'co2_ppm', 'weight']);
        
        return response()->json($data);
    }

    /**
     * Send custom command to device
     */
    public function sendCommand(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|string',
                'command' => 'required|string',
            ]);
            
            $device = IoTDevice::where('device_id', $validated['device_id'])->first();
            
            if (!$device) {
                return response()->json(['error' => 'Device not found'], 404);
            }
            
            // Parse command JSON
            $commandData = json_decode($validated['command'], true);
            if (!$commandData) {
                return response()->json(['error' => 'Invalid JSON command'], 400);
            }
            
            // Publish directly to MQTT (like controlFeeder)
            $topic = "iot/devices/{$device->device_id}/commands";
            $payload = json_encode($commandData);
            
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 1);
            $mqtt->disconnect();
            
            return response()->json([
                'success' => true,
                'message' => 'Command sent successfully',
                'command' => $commandData
            ]);
            
        } catch (\Exception $e) {
            Log::error('sendCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Control feeder (ESP32 Product V6)
     */
    public function controlFeeder(Request $request): JsonResponse
    {
        Log::info('=== FEEDER CONTROL START ===', ['request' => $request->all()]);
        
        $validated = $request->validate([
            'device_id' => 'required|string',
            'action' => 'required|in:open,close,feed,start,cancel,abort',  // Added ESP32 actions
            'amount' => 'nullable|integer|min:1|max:10',  // ESP32 limit: 1-10
        ]);
        
        Log::info('Validation passed', $validated);
        
        $device = IoTDevice::where('device_id', $validated['device_id'])->first();
        
        if (!$device) {
            Log::error('Device not found', ['device_id' => $validated['device_id']]);
            return response()->json(['error' => 'Device not found'], 404);
        }
        
        Log::info('Device found', ['id' => $device->id, 'device_id' => $device->device_id]);
        
        // Map web actions to ESP32 actions
        $actionMap = [
            'feed' => 'start',   // Web 'feed' -> ESP32 'start'
            'cancel' => 'abort', // Web 'cancel' -> ESP32 'abort'
        ];
        
        $esp32Action = $actionMap[$validated['action']] ?? $validated['action'];
        
        Log::info('Action mapped', ['web_action' => $validated['action'], 'esp32_action' => $esp32Action]);
        
        $commandData = [
            'action' => $esp32Action,
        ];
        
        if (in_array($esp32Action, ['start', 'feed']) && isset($validated['amount'])) {
            $commandData['times'] = $validated['amount'];  // ESP32 uses 'times' not 'amount'
        }
        
        // TEMPORARY: Comment out DeviceCommand to avoid 500 error
        // TODO: Fix device_commands table or create migration
        /*
        $command = DeviceCommand::create([
            'device_id' => $device->id,
            'command_type' => 'feeder_' . $esp32Action,
            'command_data' => $commandData,
            'status' => 'pending',
        ]);
        
        Log::info('Command record created', ['command_id' => $command->id]);
        */
        
        // Publish to MQTT using ESP32 format
        Log::info('About to publish MQTT command');
        
        // Use DIRECT MQTT facade like working project (NO MqttService!)
        try {
            $topic = "iot/devices/{$device->device_id}/commands";
            
            $commandId = mt_rand(1000, 9999);
            $payload = json_encode([
                'id' => $commandId,
                'cmd' => 'feed ' . $esp32Action,
                'params' => isset($validated['amount']) ? ['times' => $validated['amount']] : []
            ]);
            
            Log::info('Publishing directly to MQTT', [
                'topic' => $topic,
                'payload' => $payload
            ]);
            
            // DIRECT MQTT - like working project!
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 1);  // QoS 1
            $mqtt->disconnect();
            
            Log::info('MQTT publish SUCCESS via direct facade!');
            
        } catch (\Exception $e) {
            Log::error('Direct MQTT publish failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // $command->markAsSent();  // Commented - no $command variable
        
        Log::info('=== FEEDER CONTROL END ===');
        
        return response()->json([
            'success' => true,
            'message' => 'Feeder command sent successfully',
            'esp32_action' => $esp32Action,
        ]);
    }

    /**
     * Update actuator (ESP32 Product V6)
     */
    public function updateActuator(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'actuator' => 'required|in:fan,humidifier,heater',
            'value' => 'nullable|integer|min:0|max:100',  // For fan percent
            'mode' => 'nullable|in:on,off,auto',          // ESP32 modes
        ]);
        
        $device = IoTDevice::where('device_id', $validated['device_id'])->first();
        
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }
        
        $actuator = $validated['actuator'];
        $result = false;
        
        // Use ESP32-specific command methods
        switch ($actuator) {
            case 'fan':
                // Fan uses percent value
                $percent = $validated['value'] ?? 50;
                $result = MqttService::sendFanCommand($device->device_id, $percent);
                break;
                
            case 'heater':
                // Heater uses mode (on/off/auto)
                $mode = $validated['mode'] ?? 'auto';
                $result = MqttService::sendHeaterCommand($device->device_id, $mode);
                break;
                
            case 'humidifier':
                // Humidifier uses mode (on/off/auto)
                $mode = $validated['mode'] ?? 'auto';
                $result = MqttService::sendHumidifierCommand($device->device_id, $mode);
                break;
        }
        
        if ($result) {
            // Log command
            DeviceCommand::create([
                'device_id' => $device->id,
                'command_type' => "actuator_{$actuator}",
                'command_data' => $validated,
                'status' => 'sent',
            ]);
        }
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Actuator command sent successfully' : 'Failed to send command',
            'actuator' => $actuator,
        ]);
    }
    
    /**
     * Store sensor data from ESP32 (HTTP API fallback)
     */
    public function storeSensorData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'dht_temp' => 'nullable|numeric',
            'dht_rh' => 'nullable|numeric',
            'dht_hi' => 'nullable|numeric',
            'mq_odor' => 'nullable|numeric',
            'mq_vpin' => 'nullable|numeric',
            'mq_vgas' => 'nullable|numeric',
            'water_level' => 'nullable|numeric',
            'water_zone' => 'nullable|string',
            'soil_moisture' => 'nullable|numeric',
            'soil_zone' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'actuators' => 'nullable|array',
        ]);
        
        // Find or create device
        $device = IoTDevice::firstOrCreate(
            ['device_id' => $validated['device_id']],
            [
                'name' => 'ESP32 Device ' . $validated['device_id'],
                'type' => 'sensor',
                'status' => 'online',
                'last_seen' => now(),
                'is_active' => true
            ]
        );
        
        // Update device status
        $device->update([
            'status' => 'online',
            'last_seen' => now()
        ]);
        
        // Save sensor data
        SensorData::create([
            'device_id' => $device->id,
            'temperature' => $validated['dht_temp'] ?? null,
            'humidity' => $validated['dht_rh'] ?? null,
            'heat_index' => $validated['dht_hi'] ?? null,
            'odor_index' => $validated['mq_odor'] ?? null,
            'vpin' => $validated['mq_vpin'] ?? null,
            'vgas' => $validated['mq_vgas'] ?? null,
            'water_level' => $validated['water_level'] ?? null,
            'water_zone' => $validated['water_zone'] ?? null,
            'soil_moisture' => $validated['soil_moisture'] ?? null,
            'soil_zone' => $validated['soil_zone'] ?? null,
            'weight' => $validated['weight'] ?? null,
        ]);
        
        // Update actuator state if provided
        if (isset($validated['actuators']) && is_array($validated['actuators'])) {
            $device->actuatorState()->updateOrCreate(
                ['device_id' => $device->id],
                $validated['actuators']
            );
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Sensor data saved successfully',
            'device_id' => $validated['device_id']
        ]);
    }
}
