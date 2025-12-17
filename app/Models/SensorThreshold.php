<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorThreshold extends Model
{
    protected $fillable = [
        'device_id',
        'sensor_type',
        'min_value',
        'max_value',
        'enabled',
        'cooldown_minutes',
        'last_alert_at',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'enabled' => 'boolean',
        'last_alert_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IoTDevice::class, 'device_id');
    }

    /**
     * Check if value violates threshold
     */
    public function isViolated(float $value): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if ($this->min_value !== null && $value < $this->min_value) {
            return true;
        }

        if ($this->max_value !== null && $value > $this->max_value) {
            return true;
        }

        return false;
    }

    /**
     * Check if cooldown period has passed
     */
    public function canSendAlert(): bool
    {
        if (!$this->last_alert_at) {
            return true;
        }

        $minutesSinceLastAlert = $this->last_alert_at->diffInMinutes(now());
        return $minutesSinceLastAlert >= $this->cooldown_minutes;
    }

    /**
     * Record that alert was sent
     */
    public function recordAlert(): void
    {
        $this->update(['last_alert_at' => now()]);
    }

    /**
     * Get violation type (min or max)
     */
    public function getViolationType(float $value): string
    {
        if ($this->min_value !== null && $value < $this->min_value) {
            return 'below_minimum';
        }

        if ($this->max_value !== null && $value > $this->max_value) {
            return 'above_maximum';
        }

        return 'none';
    }
}
