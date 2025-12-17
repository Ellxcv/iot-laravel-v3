<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use App\Models\IoTDevice;
use App\Models\SensorData;
use App\Models\ActuatorHistory;

class MqttSubscriber extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'Subscribe to MQTT sensor data from IoT devices';

    public function handle()
    {
        $this->info('🔌 Starting MQTT Subscriber for ESP32 Product V6...');
        $this->info('📬 Subscribing to: iot/devices/+/telemetry');

        try {
            MqttService::subscribe('+/telemetry', function ($topic, $message) {
                $this->info("📨 Received from: $topic");

                try {
                    $data = json_decode($message, true);

                    // Validate message structure
                    if (!$data || !isset($data['device_id'])) {
                        $this->error('❌ Invalid message format - missing device_id');
                        $this->line('Message: ' . substr($message, 0, 200));
                        return;
                    }

                    // Find or create device (string device_id from ESP32)
                    $device = IoTDevice::firstOrCreate(
                        ['device_id' => $data['device_id']],
                        [
                            'name' => 'ESP32 Cat Cage - ' . $data['device_id'],
                            'type' => 'sensor',
                            'status' => 'online',
                            'last_seen' => now(),
                            'is_active' => true,
                        ]
                    );

                    // Update device status
                    $device->update([
                        'status' => 'online',
                        'last_seen' => now(),
                    ]);

                    // Extract nested sensors data (ESP32 format)
                    $sensors = $data['sensors'] ?? [];
                    if (empty($sensors)) {
                        $this->warn('⚠️  No sensors data in message');
                        return;
                    }

                    // Save sensors
                    SensorData::create([
                        'device_id' => $device->id,

                        // DHT22
                        'temperature' => $sensors['temperature'] ?? null,
                        'humidity' => $sensors['humidity'] ?? null,
                        'heat_index' => $sensors['heat_index'] ?? null,

                        // MQ-135
                        'odor_index' => $sensors['odor'] ?? null,
                        'co2_ppm' => $sensors['co2_ppm'] ?? null,
                        'mq_baseline' => $sensors['mq_baseline'] ?? null,
                        'mq_adc' => $sensors['mq_adc'] ?? null,
                        'mq_vpin' => $sensors['mq_vpin'] ?? null,
                        'mq_vgas' => $sensors['mq_vgas'] ?? null,
                        'vpin' => $sensors['mq_vpin'] ?? null,
                        'vgas' => $sensors['mq_vgas'] ?? null,

                        // Water level
                        'water_level' => $sensors['water_level'] ?? null,
                        'wl_adc' => $sensors['wl_adc'] ?? null,
                        'wl_volt' => $sensors['wl_volt'] ?? null,
                        'wl_zone' => $sensors['wl_zone'] ?? null,
                        'water_zone' => $sensors['wl_zone'] ?? null,

                        // Soil
                        'soil_pct' => $sensors['soil_pct'] ?? null,
                        'soil_moisture' => $sensors['soil_pct'] ?? null,
                        'soil_adc' => $sensors['soil_adc'] ?? null,
                        'soil_volt' => $sensors['soil_volt'] ?? null,
                        'soil_zone' => $sensors['soil_zone'] ?? null,

                        // Weight
                        'weight' => $sensors['weight_grams'] ?? null,
                    ]);

                    // ===== Actuator state (FIXED mapping + FIXED mode) =====
                    // Extract actuator status (ESP32 format)
                    $status = $data['status'] ?? [];
                                    
                    // Update actuator state with ESP32 format
                    if (!empty($status)) {
                    
                        // control_mode: keep as string (FUZZY/HYST/MANUAL/etc)
                        $controlMode = (string)($status['control_mode'] ?? 'FUZZY');
                    
                        // duty pct (0..100)
                        $fanDutyPct    = (float)($status['fan_duty_pct'] ?? 0);
                        $heaterDutyPct = (float)($status['heater_duty_pct'] ?? 0);
                        $humidDutyPct  = (float)($status['humid_duty_pct'] ?? 0);
                    
                        // relay state tinyint(1) 0/1
                        $humidOn = (int)(bool)($status['humidifier_on'] ?? false);
                        $heaterOn= (int)(bool)($status['heater_on'] ?? false);
                    
                        $device->actuatorState()->updateOrCreate(
                            ['device_id' => $device->id], // FK to iot_devices.id
                            [
                                'fan_duty_pct'    => $fanDutyPct,
                                'heater_duty_pct' => $heaterDutyPct,
                                'humid_duty_pct'  => $humidDutyPct,
                                'humidifier_on'   => $humidOn,
                                'heater_on'       => $heaterOn,
                                'control_mode'    => $controlMode,
                            ]
                        );
                    
                        // Also save to actuator history for historical tracking
                        ActuatorHistory::create([
                            'device_id'        => $device->id,
                            'fan_duty_pct'     => $fanDutyPct,
                            'heater_duty_pct'  => $heaterDutyPct,
                            'humid_duty_pct'   => $humidDutyPct,
                            'humidifier_on'    => $humidOn,
                            'heater_on'        => $heaterOn,
                            'control_mode'     => $controlMode,
                        ]);
                    }

                    // ===== end actuator =====

                    // Success log
                    $temp  = $sensors['temperature'] ?? 'N/A';
                    $humid = $sensors['humidity'] ?? 'N/A';
                    $co2   = $sensors['co2_ppm'] ?? 'N/A';

                    $this->info("✅ Telemetry saved - Device: {$data['device_id']} | T: {$temp}°C | H: {$humid}% | CO2: {$co2}ppm");

                } catch (\Exception $e) {
                    $this->error('❌ Error processing message: ' . $e->getMessage());
                    $this->line('Stack trace: ' . $e->getTraceAsString());
                }
            });

        } catch (\Exception $e) {
            $this->error('❌ MQTT Subscribe failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
