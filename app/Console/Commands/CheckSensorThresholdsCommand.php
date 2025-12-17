<?php

namespace App\Console\Commands;

use App\Models\IoTDevice;
use App\Models\SensorThreshold;
use App\Application\DTOs\SendNotificationDTO;
use App\Application\UseCases\Notification\SendNotificationUseCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSensorThresholdsCommand extends Command
{
    protected $signature = 'sensor:check-thresholds';
    protected $description = 'Check sensor readings against thresholds and send alerts';

    public function __construct(
        private SendNotificationUseCase $sendNotificationUseCase
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking sensor thresholds...');

        $devices = IoTDevice::with(['latestSensorData', 'user'])->get();
        $alertsSent = 0;

        foreach ($devices as $device) {
            if (!$device->latestSensorData || !$device->user) {
                continue;
            }

            $thresholds = SensorThreshold::where('device_id', $device->id)
                ->where('enabled', true)
                ->get();

            foreach ($thresholds as $threshold) {
                $value = $this->getSensorValue($device->latestSensorData, $threshold->sensor_type);
                
                if ($value === null) {
                    continue;
                }

                // Check if threshold is violated
                if ($threshold->isViolated($value) && $threshold->canSendAlert()) {
                    $this->sendAlert($device, $threshold, $value);
                    $threshold->recordAlert();
                    $alertsSent++;
                }
            }
        }

        $this->info("Sensor check complete. Sent {$alertsSent} alerts.");
        return 0;
    }

    private function getSensorValue($sensorData, string $type): ?float
    {
        return match($type) {
            'temperature' => $sensorData->temperature,
            'humidity' => $sensorData->humidity,
            'air_quality' => $sensorData->air_quality,
            default => null,
        };
    }

    private function sendAlert(IoTDevice $device, SensorThreshold $threshold, float $value): void
    {
        $violationType = $threshold->getViolationType($value);
        $sensorName = ucfirst(str_replace('_', ' ', $threshold->sensor_type));
        
        $message = $this->formatAlertMessage($device, $sensorName, $value, $threshold, $violationType);

        try {
            $dto = SendNotificationDTO::fromArray([
                'user_id' => $device->user_id,
                'type' => 'sensor_alert_' . $threshold->sensor_type,
                'message' => $message,
            ]);

            $this->sendNotificationUseCase->execute($dto);
            
            Log::info("Sensor alert sent", [
                'device' => $device->name,
                'sensor' => $threshold->sensor_type,
                'value' => $value,
                'violation' => $violationType,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send sensor alert: " . $e->getMessage());
        }
    }

    private function formatAlertMessage(
        IoTDevice $device,
        string $sensorName,
        float $value,
        SensorThreshold $threshold,
        string $violationType
    ): string {
        $unit = match($threshold->sensor_type) {
            'temperature' => '°C',
            'humidity' => '%',
            'air_quality' => ' PPM',
            default => '',
        };

        $emoji = match($threshold->sensor_type) {
            'temperature' => '🌡️',
            'humidity' => '💧',
            'air_quality' => '💨',
            default => '⚠️',
        };

        $status = $violationType === 'below_minimum' ? 'TOO LOW' : 'TOO HIGH';
        $limit = $violationType === 'below_minimum' 
            ? "Min: {$threshold->min_value}{$unit}" 
            : "Max: {$threshold->max_value}{$unit}";

        return "{$emoji} SENSOR ALERT: {$sensorName} {$status}!\n\n"
            . "Device: {$device->name}\n"
            . "Current: {$value}{$unit}\n"
            . "Threshold: {$limit}\n"
            . "Time: " . now()->format('Y-m-d H:i:s');
    }
}
