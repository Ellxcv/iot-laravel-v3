<?php

namespace App\Application\UseCases\Camera;

use App\Application\DTOs\CameraCommandDTO;
use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Facades\Log;

class PublishCameraCommandUseCase
{
    public function execute(CameraCommandDTO $command): bool
    {
        try {
            $mqtt = MQTT::connection();
            $topic = $command->getTopic();
            $payload = json_encode($command->toMqttPayload());
            
            $mqtt->publish($topic, $payload, 1);
            $mqtt->disconnect();

            Log::info('Camera command published', [
                'device_id' => $command->deviceId,
                'command' => $command->cmd,
                'params' => $command->params,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to publish camera command', [
                'device_id' => $command->deviceId,
                'command' => $command->cmd,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
