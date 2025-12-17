<?php

namespace App\Services;

use PhpMqtt\Client\Facades\MQTT;
use Exception;
use Illuminate\Support\Facades\Log;

class MqttService
{
    /**
     * Publish message to MQTT topic
     * 
     * @param string $topic Topic to publish to (will be prefixed with MQTT_TOPIC_PREFIX)
     * @param string|array $message Message to publish (will be JSON encoded if array)
     * @param int|null $qos Quality of Service (0, 1, or 2). Uses config default if null.
     * @param bool $retain Whether message should be retained
     * @return bool Success status
     */
    public static function publish(string $topic, $message, ?int $qos = null, bool $retain = false): bool
{
    try {
        Log::info('=== MQTT PUBLISH START ===', [
            'topic' => $topic,
            'message' => is_array($message) ? $message : substr($message, 0, 200),
        ]);
        
        // Get topic prefix from config
        $prefix = config('mqtt.topic_prefix', 'iot/devices');
        $fullTopic = $prefix ? "{$prefix}/{$topic}" : $topic;
        
        Log::info('Topic prepared', ['full_topic' => $fullTopic]);
        
        // Convert array to JSON
        if (is_array($message)) {
            $message = json_encode($message);
        }
        
        // Use default QoS from config if not specified
        $qos = $qos ?? config('mqtt.qos', 1);
        
        Log::info('About to get MQTT connection', [
            'host' => config('mqtt.host'),
            'port' => config('mqtt.port'),
            'username' => config('mqtt.username') ? 'SET' : 'NOT SET',
        ]);
        
        // Get MQTT connection
        $mqtt = self::getConnection();
        
        Log::info('MQTT connection obtained');
        
        // Publish message
        $mqtt->publish($fullTopic, $message, $qos, $retain);
        
        Log::info('Message published to MQTT');
        
        // Close connection
        $mqtt->disconnect();
        
        Log::info('MQTT disconnected successfully');
        
        return true;
        
    } catch (\Exception $e) {
        Log::error('MQTT Publish Failed', [
            'topic' => $topic ?? 'unknown',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return false;
    }
}
    
    /**
     * Subscribe to MQTT topic
     * 
     * @param string $topic Topic to subscribe to
     * @param callable $callback Callback function to handle messages
     * @param int|null $qos Quality of Service
     * @return bool Success status
     */
    public static function subscribe(string $topic, callable $callback, ?int $qos = null): bool
    {
        try {
            $prefix = config('mqtt.topic_prefix', 'iot/devices');
            $fullTopic = $prefix ? "{$prefix}/{$topic}" : $topic;
            $qos = $qos ?? config('mqtt.qos', 1);
            
            $mqtt = self::getConnection();
            
            $mqtt->subscribe($fullTopic, $callback, $qos);
            $mqtt->loop(true);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('MQTT Subscribe Failed', [
                'topic' => $topic,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Get MQTT connection with proper configuration
     * Uses UNIQUE client ID for each connection to avoid conflicts
     * 
     * @return \PhpMqtt\Client\MqttClient
     */
    private static function getConnection()
    {
        // Generate UNIQUE client ID to avoid conflicts with subscriber
        // Subscriber uses 'laravel-subscriber', publishers use 'laravel-pub-XXXXX'
        $uniqueClientId = 'laravel-pub-' . uniqid() . '-' . mt_rand(1000, 9999);
        
        // Create new connection with unique ID
        // NOTE: We can't use MQTT::connection() facade as it uses config client_id
        // which would conflict with subscriber
        config(['mqtt-client.connections.default.client_id' => $uniqueClientId]);
        
        return MQTT::connection();
    }
    
    /**
     * Publish command to device
     * 
     * @param string $deviceId Device ID
     * @param array $command Command data
     * @return bool Success status
     */
    public static function publishCommand(string $deviceId, array $command): bool
    {
        return self::publish("{$deviceId}/commands", $command);
    }
    
    /**
     * Publish feeder command to device
     * 
     * @param string $deviceId Device ID
     * @param string $action Action (open, close, feed, cancel)
     * @param int|null $amount Amount (for feed action)
     * @return bool Success status
     */
    public static function publishFeederCommand(string $deviceId, string $action, ?int $amount = null): bool
    {
        $command = [
            'type' => 'feeder',
            'action' => $action,
            'timestamp' => now()->toIso8601String()
        ];
        
        if ($action === 'feed' && $amount) {
            $command['amount'] = $amount;
        }
        
        return self::publishCommand($deviceId, $command);
    }
    
    /**
     * Publish actuator update to device
     * 
     * @param string $deviceId Device ID
     * @param string $actuator Actuator name (fan, humidifier, heater)
     * @param array $settings Actuator settings
     * @return bool Success status
     */
    public static function publishActuatorUpdate(string $deviceId, string $actuator, array $settings): bool
    {
        $command = [
            'type' => 'actuator',
            'actuator' => $actuator,
            'settings' => $settings,
            'timestamp' => now()->toIso8601String()
        ];
        
        return self::publishCommand($deviceId, $command);
    }
    
    // ========== ESP32 Product V6 Specific Methods ==========
    
    /**
     * Publish command to ESP32 using Product V6 format
     * Format: {"id": 123, "cmd": "command_name", "params": {...}}
     * 
     * @param string $deviceId Device ID
     * @param string $cmd Command string
     * @param array $params Optional parameters
     * @return bool Success status
     */
    public static function publishESP32Command(string $deviceId, string $cmd, array $params = []): bool
    {
        $command = [
            'id' => rand(1, 9999),
            'cmd' => $cmd,
        ];
        
        if (!empty($params)) {
            $command['params'] = $params;
        }
        
        return self::publish("{$deviceId}/commands", $command);
    }
    
    /**
     * Send feeder command (ESP32 Product V6)
     *
     * @param string $deviceId Device ID (e.g., 'esp32-catcage-01')
     * @param string $action Action: 'open', 'close', 'start', 'abort'
     * @param int|null $times Number of times for 'start' action (1-10)
     * @return bool Success status
     */
    public static function sendFeederCommand(string $deviceId, string $action, ?int $times = null): bool
    {
        try {
            Log::info('sendFeederCommand called', [
                'device_id' => $deviceId,
                'action' => $action,
                'times' => $times,
                'context' => php_sapi_name() // CLI or FPM
            ]);
            
            // Generate unique command ID
            $commandId = mt_rand(1000, 9999);
            
            // Build command payload matching ESP32 format
            $payload = [
                'id' => $commandId,
                'cmd' => 'feed ' . $action,  // e.g., "feed open", "feed close"
            ];
            
            // Add times parameter for 'start' action
            if ($action === 'start' && $times !== null) {
                $payload['times'] = min(max($times, 1), 10);  // Clamp 1-10
            }
            
            Log::info('Payload built', ['payload' => $payload]);
            
            // Publish to device's command topic
            $topic = $deviceId . '/commands';
            
            Log::info('About to publish', [
                'topic' => $topic,
                'payload' => json_encode($payload)
            ]);
            
            $result = self::publish($topic, $payload);
            
            Log::info('Publish result', ['result' => $result]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('sendFeederCommand exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Send heater command to ESP32
     * Supported modes: on, off, auto
     * 
     * @param string $deviceId Device ID
     * @param string $mode Mode (on/off/auto)
     * @return bool Success status
     */
    public static function sendHeaterCommand(string $deviceId, string $mode): bool
    {
        $cmdMap = [
            'on' => 'heat on',
            'off' => 'heat off',
            'auto' => 'heat auto',
        ];
        
        $cmd = $cmdMap[$mode] ?? $mode;
        
        return self::publishESP32Command($deviceId, $cmd);
    }
    
    /**
     * Send humidifier command to ESP32
     * Supported modes: on, off, auto
     * 
     * @param string $deviceId Device ID
     * @param string $mode Mode (on/off/auto)
     * @return bool Success status
     */
    public static function sendHumidifierCommand(string $deviceId, string $mode): bool
    {
        $cmdMap = [
            'on' => 'hum on',
            'off' => 'hum off',
            'auto' => 'hum auto',
        ];
        
        $cmd = $cmdMap[$mode] ?? $mode;
        
        return self::publishESP32Command($deviceId, $cmd);
    }
    
    /**
     * Send fan control command to ESP32
     * 
     * @param string $deviceId Device ID
     * @param int $percent Fan duty percentage (0-100)
     * @return bool Success status
     */
    public static function sendFanCommand(string $deviceId, int $percent): bool
    {
        // Clamp percent to 0-100 range
        $percent = max(0, min(100, $percent));
        
        return self::publishESP32Command($deviceId, 'fan', ['percent' => $percent]);
    }
    
    /**
     * Send servo position command to ESP32
     * 
     * @param string $deviceId Device ID
     * @param int $angle Servo angle (0-180 degrees)
     * @return bool Success status
     */
    public static function sendServoCommand(string $deviceId, int $angle): bool
    {
        // Clamp angle to 0-180 range
        $angle = max(0, min(180, $angle));
        
        return self::publishESP32Command($deviceId, 'servo', ['angle' => $angle]);
    }
}
