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
    ];

    protected $casts = [
        'notification_enabled' => 'boolean',
        'last_notified_at' => 'datetime',
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
     * Check if enough time has passed since last notification
     * Prevents spam by ensuring at least 1 hour between notifications
     */
    public function isNotificationDue(): bool
    {
        if (!$this->last_notified_at) {
            return true;
        }

        // Allow notification if last one was sent more than 1 hour ago
        return $this->last_notified_at->diffInMinutes(now()) >= 60;
    }

    /**
     * Record that a notification was sent
     */
    public function recordNotificationSent(): void
    {
        $this->update(['last_notified_at' => now()]);
    }

    /**
     * Get offline timeout in minutes
     */
    public function getTimeoutMinutes(): int
    {
        return $this->offline_timeout_minutes;
    }
}
