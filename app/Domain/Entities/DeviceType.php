<?php

namespace App\Domain\Entities;

enum DeviceType: string
{
    case ESP32 = 'esp32';
    case ARDUINO = 'arduino';
    case RASPBERRY_PI = 'raspberry_pi';
    case SENSOR = 'sensor';  // Added for existing data
    case SENSOR_NODE = 'sensor_node';
    case ACTUATOR = 'actuator';
    case GATEWAY = 'gateway';
    case CAMERA = 'camera';  // ESP32 CAM devices
    case IOT = 'iot';  // Generic IoT device

    public function label(): string
    {
        return match($this) {
            self::ESP32 => 'ESP32',
            self::ARDUINO => 'Arduino',
            self::RASPBERRY_PI => 'Raspberry Pi',
            self::SENSOR => 'Sensor',
            self::SENSOR_NODE => 'Sensor Node',
            self::ACTUATOR => 'Actuator',
            self::GATEWAY => 'Gateway',
            self::CAMERA => 'Camera',
            self::IOT => 'IoT Device',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
