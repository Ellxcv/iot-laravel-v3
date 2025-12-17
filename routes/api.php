<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IoTController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Sensor Data Endpoint (for ESP32 HTTP fallback)
Route::post('/sensor-data', [IoTController::class, 'storeSensor Data']);

// Camera Image Upload from ESP32
Route::post('/camera/upload', [App\Http\Controllers\CameraController::class, 'uploadImage']);
