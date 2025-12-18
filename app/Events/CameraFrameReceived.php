<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when ESP32 CAM sends a new frame via MQTT
 * Broadcasts frame to WebSocket channel for real-time streaming
 */
class CameraFrameReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $deviceId;
    public string $frameData; // base64 JPEG string
    public int $timestamp;
    public float $fps;

    /**
     * Create a new event instance.
     */
    public function __construct(string $deviceId, string $frameData, int $timestamp, float $fps = 0)
    {
        $this->deviceId = $deviceId;
        $this->frameData = $frameData;
        $this->timestamp = $timestamp;
        $this->fps = $fps;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel("camera.{$this->deviceId}");
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'CameraFrameReceived';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'deviceId' => $this->deviceId,
            'frameData' => $this->frameData,
            'timestamp' => $this->timestamp,
            'fps' => $this->fps,
        ];
    }
}
