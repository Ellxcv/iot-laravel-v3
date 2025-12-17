<?php

namespace App\Application\DTOs;

class CameraCommandDTO
{
    public function __construct(
        public readonly string $deviceId,
        public readonly string $cmd,
        public readonly array $params = [],
    ) {}

    public static function capture(string $deviceId): self
    {
        return new self($deviceId, 'capture');
    }

    public static function streamStart(string $deviceId): self
    {
        return new self($deviceId, 'stream_start');
    }

    public static function streamStop(string $deviceId): self
    {
        return new self($deviceId, 'stream_stop');
    }

    public static function flashOn(string $deviceId): self
    {
        return new self($deviceId, 'flash_on');
    }

    public static function flashOff(string $deviceId): self
    {
        return new self($deviceId, 'flash_off');
    }

    public static function setQuality(string $deviceId, int $quality): self
    {
        return new self($deviceId, 'set_quality', ['quality' => $quality]);
    }

    public static function setResolution(string $deviceId, string $resolution): self
    {
        return new self($deviceId, 'set_resolution', ['resolution' => $resolution]);
    }

    public function toMqttPayload(): array
    {
        $payload = ['cmd' => $this->cmd];
        
        if (!empty($this->params)) {
            $payload['params'] = $this->params;
        }
        
        return $payload;
    }

    public function getTopic(): string
    {
        return "iot/devices/{$this->deviceId}/commands";
    }
}
