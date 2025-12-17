<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Guest routes (only accessible when not authenticated)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard redirects to IoT Status
    Route::get('/dashboard', function () {
        return redirect()->route('iot.status');
    })->name('dashboard');
    
    // IoT Routes
    Route::get('/iot/status', [App\Http\Controllers\IoTController::class, 'status'])->name('iot.status');
    Route::get('/iot/sensor-data', [App\Http\Controllers\IoTController::class, 'getSensorData']);
    Route::get('/iot/historical-data', [App\Http\Controllers\IoTController::class, 'getHistoricalData']);
    Route::post('/iot/send-command', [App\Http\Controllers\IoTController::class, 'sendCommand']);
    Route::post('/iot/control-feeder', [App\Http\Controllers\IoTController::class, 'controlFeeder']);
    Route::post('/iot/update-actuator', [App\Http\Controllers\IoTController::class, 'updateActuator']);
    
    // Device Management Routes
    Route::get('/devices', [App\Http\Controllers\DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [App\Http\Controllers\DeviceController::class, 'store'])->name('devices.store');
    Route::patch('/devices/{id}/status', [App\Http\Controllers\DeviceController::class, 'updateStatus'])->name('devices.updateStatus');
    Route::delete('/devices/{id}', [App\Http\Controllers\DeviceController::class, 'destroy'])->name('devices.destroy');
    
    // Camera Routes
    Route::prefix('camera')->name('camera.')->group(function () {
        Route::get('/live', [App\Http\Controllers\CameraController::class, 'live'])->name('live');
        Route::get('/{deviceId}/gallery', [App\Http\Controllers\CameraController::class, 'gallery'])->name('gallery');
        Route::post('/{deviceId}/capture', [App\Http\Controllers\CameraController::class, 'capture'])->name('capture');
        Route::post('/{deviceId}/stream/start', [App\Http\Controllers\CameraController::class, 'startStream'])->name('stream.start');
        Route::post('/{deviceId}/stream/stop', [App\Http\Controllers\CameraController::class, 'stopStream'])->name('stream.stop');
        Route::post('/{deviceId}/flash', [App\Http\Controllers\CameraController::class, 'flashControl'])->name('flash');
        Route::post('/{deviceId}/quality', [App\Http\Controllers\CameraController::class, 'setQuality'])->name('quality');
        Route::post('/{deviceId}/resolution', [App\Http\Controllers\CameraController::class, 'setResolution'])->name('resolution');
        Route::get('/{deviceId}/latest', [App\Http\Controllers\CameraController::class, 'latest'])->name('latest');
        Route::get('/{deviceId}/status', [App\Http\Controllers\CameraController::class, 'getDeviceStatus'])->name('status');
    });
    
    // Notification Routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/settings', [App\Http\Controllers\NotificationController::class, 'updateSettings'])->name('notifications.update');
    Route::post('/notifications/test', [App\Http\Controllers\NotificationController::class, 'sendTest'])->name('notifications.test');
    Route::get('/notifications/logs', [App\Http\Controllers\NotificationController::class, 'getLogs'])->name('notifications.logs');
    
    // FCM Token API Routes
    Route::post('/api/fcm-tokens', [App\Http\Controllers\Api\FcmTokenController::class, 'store']);
    Route::get('/api/fcm-tokens', [App\Http\Controllers\Api\FcmTokenController::class, 'index']);
    Route::delete('/api/fcm-tokens/{id}', [App\Http\Controllers\Api\FcmTokenController::class, 'destroy']);
    Route::post('/api/fcm-tokens/cleanup', [App\Http\Controllers\Api\FcmTokenController::class, 'cleanup']);
    
    // Threshold Routes
    Route::get('/devices/{deviceId}/thresholds', [App\Http\Controllers\ThresholdController::class, 'index']);
    Route::post('/devices/{deviceId}/thresholds', [App\Http\Controllers\ThresholdController::class, 'update']);
    
    // History Routes
    Route::get('/history', [App\Http\Controllers\HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/data', [App\Http\Controllers\HistoryController::class, 'getData'])->name('history.data');
    
    // Admin Routes (Admin Only)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // User Management
        Route::resource('users', App\Http\Controllers\UserController::class);
        
        // Device Management
        Route::resource('devices', App\Http\Controllers\AdminDeviceController::class);
    });
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Redirect root to login or IoT Status
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('iot.status') 
        : redirect()->route('login');
});
