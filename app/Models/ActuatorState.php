<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActuatorState extends Model
{
    protected $table = 'actuator_states';

    protected $fillable = [
            'device_id',
            'fan_duty_pct',
            'heater_duty_pct',
            'humid_duty_pct',
            'humidifier_on',
            'heater_on',
            'control_mode',
    ];

    protected $casts = [
        'fan_duty_pct' => 'float',
        'humidifier_duty_pct' => 'float',
        'heater_duty_pct' => 'float',
        'humidifier_on' => 'boolean',
        'heater_on' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        // kalau actuator_states.device_id adalah FK ke iot_devices.id (integer)
        return $this->belongsTo(IoTDevice::class, 'device_id');
    }
}
