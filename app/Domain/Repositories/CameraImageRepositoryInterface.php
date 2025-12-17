<?php

namespace App\Domain\Repositories;

use App\Application\DTOs\CameraImageDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface CameraImageRepositoryInterface
{
    /**
     * Create new camera image record
     */
    public function create(CameraImageDTO $dto): int;

    /**
     * Find latest image for device
     */
    public function findLatestByDevice(string $deviceId): ?array;

    /**
     * Get paginated images for device
     */
    public function paginateByDevice(string $deviceId, int $perPage = 20): LengthAwarePaginator;

    /**
     * Count images by device
     */
    public function countByDevice(string $deviceId): int;

    /**
     * Delete images older than specified date
     */
    public function deleteOlderThan(\DateTimeInterface $date): int;
}
