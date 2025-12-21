<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceOfflineSetting extends Model
{
    protected $fillable = [
        'user_id',
        'offline_timeout_minutes',
        'notification_enabled',
        'last_notified_at',
        'last_notified_devices',
    ];

    protected $casts = [
        'notification_enabled' => 'boolean',
        'last_notified_at' => 'datetime',
        'last_notified_devices' => 'array', // JSON to array
    ];

    /**
     * Get the user that owns this setting
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification is enabled
     */
    public function canSendNotification(): bool
    {
        return $this->notification_enabled;
    }

    /**
     * Check if enough time has passed since last notification FOR A SPECIFIC DEVICE
     * Prevents spam by ensuring at least 1 hour between notifications per device
     */
    public function isDeviceNotificationDue(string $deviceId): bool
    {
        $devices = $this->last_notified_devices ?? [];
        
        // If device never notified, it's due
        if (!isset($devices[$deviceId])) {
            return true;
        }

        // Check if last notification was more than 1 hour ago
        $lastNotified = \Carbon\Carbon::parse($devices[$deviceId]);
        return $lastNotified->diffInMinutes(now()) >= 60;
    }

    /**
     * Record that a notification was sent for a specific device
     */
    public function recordDeviceNotificationSent(string $deviceId): void
    {
        $devices = $this->last_notified_devices ?? [];
        $devices[$deviceId] = now()->toDateTimeString();
        
        $this->update([
            'last_notified_at' => now(), // Keep for backwards compatibility
            'last_notified_devices' => $devices
        ]);
    }

    /**
     * Reset notification timestamp for a specific device (when it comes online)
     */
    public function resetDeviceNotificationTimer(string $deviceId): void
    {
        $devices = $this->last_notified_devices ?? [];
        
        if (isset($devices[$deviceId])) {
            unset($devices[$deviceId]);
            $this->update(['last_notified_devices' => $devices]);
        }
    }

    /**
     * Get offline timeout in minutes
     */
    public function getTimeoutMinutes(): int
    {
        return $this->offline_timeout_minutes;
    }

    // DEPRECATED: Keep for backwards compatibility
    public function isNotificationDue(): bool
    {
        if (!$this->last_notified_at) {
            return true;
        }
        return $this->last_notified_at->diffInMinutes(now()) >= 60;
    }

    public function recordNotificationSent(): void
    {
        $this->update(['last_notified_at' => now()]);
    }
}
