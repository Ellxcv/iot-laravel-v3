<?php

namespace App\Application\DTOs;

class CameraImageDTO
{
    public function __construct(
        public readonly string $deviceId,
        public readonly string $filename,
        public readonly string $path,
        public readonly int $size,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly string $format = 'jpeg',
        public readonly ?string $thumbnailPath = null,
        public readonly ?\DateTimeInterface $capturedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deviceId: $data['device_id'],
            filename: $data['filename'],
            path: $data['path'],
            size: $data['size'],
            width: $data['width'] ?? null,
            height: $data['height'] ?? null,
            format: $data['format'] ?? 'jpeg',
            thumbnailPath: $data['thumbnail_path'] ?? null,
            capturedAt: isset($data['captured_at']) 
                ? new \DateTime($data['captured_at']) 
                : new \DateTime(),
        );
    }

    public function toArray(): array
    {
        return [
            'device_id' => $this->deviceId,
            'filename' => $this->filename,
            'path' => $this->path,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'format' => $this->format,
            'thumbnail_path' => $this->thumbnailPath,
            'captured_at' => $this->capturedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
