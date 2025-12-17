<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    protected $table = 'notification_settings';

    protected $fillable = [
        'user_id',
        'bot_token',
        'chat_id',
        'enabled',
        'fcm_device_token',
        'firebase_enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'firebase_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
