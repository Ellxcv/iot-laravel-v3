<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorData extends Model
{
    protected $table = 'sensor_data';

    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'heat_index',
        'odor_index',
        'co2_ppm',           // NEW: CO2 PPM from MQ-135
        'mq_baseline',       // NEW: MQ-135 baseline voltage
        'mq_adc',            // NEW: MQ-135 raw ADC
        'mq_vpin',           // NEW: MQ-135 pin voltage
        'mq_vgas',           // NEW: MQ-135 gas voltage
        'vpin',
        'vgas',
        'wl_adc',            // NEW: Water level ADC
        'wl_volt',           // NEW: Water level voltage
        'water_level',
        'wl_zone',           // NEW: Water zone (DRY/LOW/MID/HIGH)
        'water_zone',
        'soil_adc',          // NEW: Soil ADC
        'soil_volt',         // NEW: Soil voltage
        'soil_moisture',
        'soil_pct',          // NEW: Soil percentage (ESP32 format)
        'soil_zone',
        'weight',
    ];

    protected $casts = [
        'temperature' => 'float',
        'humidity' => 'float',
        'heat_index' => 'float',
        'odor_index' => 'float',
        'co2_ppm' => 'float',
        'mq_baseline' => 'float',
        'mq_adc' => 'float',
        'mq_vpin' => 'float',
        'mq_vgas' => 'float',
        'vpin' => 'float',
        'vgas' => 'float',
        'wl_adc' => 'float',
        'wl_volt' => 'float',
        'water_level' => 'float',
        'soil_adc' => 'float',
        'soil_volt' => 'float',
        'soil_moisture' => 'float',
        'soil_pct' => 'float',
        'weight' => 'float',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IoTDevice::class, 'device_id');
    }
}
