<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\CameraImageRepositoryInterface;
use App\Application\DTOs\CameraImageDTO;
use App\Models\CameraImage;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentCameraImageRepository implements CameraImageRepositoryInterface
{
    public function create(CameraImageDTO $dto): int
    {
        $image = CameraImage::create($dto->toArray());
        return $image->id;
    }

    public function findLatestByDevice(string $deviceId): ?array
    {
        $image = CameraImage::where('device_id', $deviceId)
            ->latest('captured_at')
            ->first();

        if (!$image) {
            return null;
        }

        return [
            'id' => $image->id,
            'device_id' => $image->device_id,
            'filename' => $image->filename,
            'path' => $image->path,
            'size' => $image->size,
            'width' => $image->width,
            'height' => $image->height,
            'format' => $image->format,
            'url' => $image->url,
            'thumbnail_url' => $image->thumbnail_url,
            'captured_at' => $image->captured_at->toIso8601String(),
        ];
    }

    public function paginateByDevice(string $deviceId, int $perPage = 20): LengthAwarePaginator
    {
        return CameraImage::where('device_id', $deviceId)
            ->orderBy('captured_at', 'desc')
            ->paginate($perPage);
    }

    public function countByDevice(string $deviceId): int
    {
        return CameraImage::where('device_id', $deviceId)->count();
    }

    public function deleteOlderThan(\DateTimeInterface $date): int
    {
        return CameraImage::where('captured_at', '<', $date)->delete();
    }
}
