<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CameraDevice extends Model
{
    protected $table = 'camera_devices';

    protected $fillable = [
        'device_id',
        'name',
        'stream_url',
        'type',
        'status',
        'resolution',
        'fps',
        'description',
        'last_seen',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'fps' => 'integer',
    ];
}
