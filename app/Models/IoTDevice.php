<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IoTDevice extends Model
{
    protected $table = 'iot_devices';

    protected $fillable = [
        'device_id',
        'name',
        'type',
        'description',
        'user_id',
        'control_mode',
        'status',
        'is_active',
        'last_seen',
        'ip',
        'fps',
        'last_image_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
        'last_image_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get sensor data for this device
     */
    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorData::class, 'device_id', 'device_id');
    }

    /**
     * Get latest sensor data for this device
     */
    public function latestSensorData()
    {
        return $this->hasOne(SensorData::class, 'device_id', 'id')  // FIXED: use integer id, not string device_id
            ->latestOfMany();
    }

    /**
     * Get actuator state for this device
     */
    public function actuatorState()
    {
        return $this->hasOne(ActuatorState::class, 'device_id', 'id');
    }

    /**
     * Get the user that owns this device
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active devices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only online devices
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Check if device is online (has sent data within last 5 minutes)
     */
    public function isOnline(): bool
    {
        if (!$this->last_seen) {
            return false;
        }
        
        // Device is online if last_seen is within 5 minutes
        return $this->last_seen->greaterThan(now()->subMinutes(5));
    }

    /**
     * Check if device is active (enabled)
     */
    public function isEnabled(): bool
    {
        return $this->is_active;
    }

    /**
     * Get camera images for this device
     */
    public function cameraImages(): HasMany
    {
        return $this->hasMany(CameraImage::class, 'device_id', 'device_id');
    }

    /**
     * Get latest camera image
     */
    public function latestImage()
    {
        return $this->hasOne(CameraImage::class, 'device_id', 'device_id')
            ->latestOfMany('captured_at');
    }
}
