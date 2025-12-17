<?php

namespace App\Application\UseCases\Camera;

use App\Domain\Repositories\CameraImageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GetCameraImagesUseCase
{
    public function __construct(
        private CameraImageRepositoryInterface $cameraImageRepository
    ) {}

    public function execute(string $deviceId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->cameraImageRepository->paginateByDevice($deviceId, $perPage);
    }

    public function getLatest(string $deviceId): ?array
    {
        return $this->cameraImageRepository->findLatestByDevice($deviceId);
    }
}
