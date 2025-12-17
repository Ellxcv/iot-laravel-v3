<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActuatorHistory extends Model
{
    protected $table = 'actuator_history';

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
        'heater_duty_pct' => 'float',
        'humid_duty_pct' => 'float',
        'humidifier_on' => 'boolean',
        'heater_on' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the device that owns this actuator history record
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(IoTDevice::class, 'device_id');
    }
}
