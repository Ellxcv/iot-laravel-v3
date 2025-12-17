<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Models\IoTDevice;
use Illuminate\Support\Facades\Log;

class CameraMqttSubscriber extends Command
{
    protected $signature = 'mqtt:camera-subscribe';
    protected $description = 'Subscribe to ESP32 CAM MQTT topics';

    public function handle()
    {
        $this->info('Starting ESP32 CAM MQTT subscriber...');

        $reconnectAttempts = 0;
        $maxReconnectAttempts = 999; // Unlimited reconnects

        while ($reconnectAttempts < $maxReconnectAttempts) {
            try {
                $this->connectAndListen();
            } catch (\Exception $e) {
                $reconnectAttempts++;
                $this->error("Connection lost: {$e->getMessage()}");
                $this->warn("Reconnecting in 10 seconds... (Attempt {$reconnectAttempts})");
                
                // Wait longer before reconnecting to avoid rapid reconnection storms
                sleep(10);
            }
        }
    }

    private function connectAndListen()
    {
        // Create dedicated subscriber connection with unique client ID
        $mqtt = new \PhpMqtt\Client\MqttClient(
            env('MQTT_HOST'),
            (int) env('MQTT_PORT', 8883),
            'laravel-camera-subscriber-' . getmypid(), // Unique client ID
            \PhpMqtt\Client\MqttClient::MQTT_3_1
        );

        $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings())
            ->setUsername(env('MQTT_AUTH_USERNAME'))
            ->setPassword(env('MQTT_AUTH_PASSWORD'))
            ->setKeepAliveInterval(60)
            ->setConnectTimeout(10)
            ->setUseTls(true)
            ->setTlsSelfSignedAllowed(true)
            ->setTlsVerifyPeer(false)
            ->setTlsVerifyPeerName(false);

        $mqtt->connect($connectionSettings, true);

        // Subscribe to camera device status
        $mqtt->subscribe('iot/devices/+/status', function (string $topic, string $message) {
            $this->handleStatusMessage($topic, $message);
        }, 1);

        // Subscribe to camera image metadata
        $mqtt->subscribe('iot/devices/+/image', function (string $topic, string $message) {
            $this->handleImageMessage($topic, $message);
        }, 1);

        $this->info('Subscribed to camera topics. Listening...');

        // This will throw exception when connection drops
        $mqtt->loop(true);
    }

    private function handleStatusMessage(string $topic, string $message)
    {
        try {
            $data = json_decode($message, true);

            if (!isset($data['device_id'])) {
                return;
            }

            $deviceId = $data['device_id'];

            // Update device status
            IoTDevice::where('device_id', $deviceId)->update([
                'status' => $data['status'] ?? 'unknown',
                'ip' => $data['ip'] ?? null,
                'fps' => $data['fps'] ?? null,
                'last_seen' => now(),
            ]);

            $this->line("[STATUS] {$deviceId}: {$data['status']} | IP: {$data['ip']} | FPS: {$data['fps']}");

            Log::info('Camera status updated', [
                'device_id' => $deviceId,
                'status' => $data['status'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process camera status', [
                'error' => $e->getMessage(),
                'topic' => $topic,
            ]);
        }
    }

    private function handleImageMessage(string $topic, string $message)
    {
        try {
            $data = json_decode($message, true);

            if (!isset($data['device_id'])) {
                return;
            }

            // Image already uploaded via HTTP, just log metadata
            Log::info('Camera image uploaded via HTTP', [
                'device_id' => $data['device_id'],
                'url' => $data['url'] ?? null,
                'uploaded' => $data['uploaded'] ?? false,
            ]);

            // Update device timestamp
            IoTDevice::where('device_id', $data['device_id'])->update([
                'last_image_at' => now(),
            ]);

            $this->line("[IMAGE] {$data['device_id']}: {$data['size']} bytes");

        } catch (\Exception $e) {
            Log::error('Failed to process camera image', [
                'error' => $e->getMessage(),
                'topic' => $topic,
            ]);
        }
    }
}
