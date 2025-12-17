<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\CameraDevice as CameraDeviceEntity;
use App\Domain\Repositories\CameraRepositoryInterface;
use App\Models\CameraDevice as CameraDeviceModel;

class EloquentCameraRepository implements CameraRepositoryInterface
{
    public function findByDeviceId(string $deviceId): ?CameraDeviceEntity
    {
        $model = CameraDeviceModel::where('device_id', $deviceId)->first();
        
        if (!$model) {
            return null;
        }
        
        return $this->modelToEntity($model);
    }

    public function findAll(): array
    {
        $models = CameraDeviceModel::all();
        
        return $models->map(function ($model) {
            return $this->modelToEntity($model);
        })->toArray();
    }

    public function save(CameraDeviceEntity $camera): CameraDeviceEntity
    {
        $model = $camera->getId()
            ? CameraDeviceModel::find($camera->getId())
            : new CameraDeviceModel();

        $model->device_id = $camera->deviceId;
        $model->name = $camera->name;
        $model->stream_url = $camera->streamUrl;
        $model->type = $camera->type;
        $model->status = $camera->status;
        $model->resolution = $camera->resolution;
        $model->fps = $camera->fps;
        $model->description = $camera->description;
        $model->last_seen = $camera->lastSeen;
        $model->save();

        return $this->modelToEntity($model);
    }

    public function deviceIdExists(string $deviceId): bool
    {
        return CameraDeviceModel::where('device_id', $deviceId)->exists();
    }

    private function modelToEntity(CameraDeviceModel $model): CameraDeviceEntity
    {
        return new CameraDeviceEntity(
            deviceId: $model->device_id,
            name: $model->name,
            streamUrl: $model->stream_url,
            type: $model->type,
            status: $model->status,
            resolution: $model->resolution,
            fps: $model->fps,
            description: $model->description,
            id: $model->id,
            lastSeen: $model->last_seen,
        );
    }
}
