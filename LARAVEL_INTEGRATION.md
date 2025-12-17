# 📸 ESP32 CAM - Laravel Integration Guide

**Documentation untuk Laravel Backend Developer**  
**Project**: Cat Cage Monitoring System  
**Last Updated**: 2025-12-16

---

## 📌 Overview

ESP32 CAM sudah diimplementasikan dengan **Hybrid Architecture**:

- **HTTP Server** (port 80): Direct access untuk streaming dan capture
- **MQTT Client**: Remote control via HiveMQ Cloud broker

Laravel backend perlu:

1. Subscribe ke MQTT topics untuk menerima status & image metadata
2. Publish commands ke ESP32 via MQTT
3. Download images dari ESP32 HTTP endpoint
4. Store data ke database
5. Provide API/UI untuk camera control

---

## 🌐 Ngrok Configuration

### Development Environment

Project Laravel menggunakan **ngrok** untuk expose local server ke public internet:

**Ngrok URL**: `https://sensationally-uninflective-porsha.ngrok-free.dev`

### Setup Ngrok

```bash
# Install ngrok (jika belum)
# Download dari https://ngrok.com/download

# Start ngrok tunnel
ngrok http 8000 --domain=sensationally-uninflective-porsha.ngrok-free.dev
```

### Laravel Configuration

Update `.env` untuk ngrok:

```env
APP_URL=https://sensationally-uninflective-porsha.ngrok-free.dev
SESSION_DOMAIN=.ngrok-free.dev
SANCTUM_STATEFUL_DOMAINS=sensationally-uninflective-porsha.ngrok-free.dev
```

### Trusted Proxies

Update `app/Http/Middleware/TrustProxies.php`:

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR |
                         Request::HEADER_X_FORWARDED_HOST |
                         Request::HEADER_X_FORWARDED_PORT |
                         Request::HEADER_X_FORWARDED_PROTO |
                         Request::HEADER_X_FORWARDED_AWS_ELB;
}
```

### Ngrok Security Headers

Ngrok menambahkan header `ngrok-skip-browser-warning`. Tambahkan middleware untuk handle ini:

```php
<?php
// app/Http/Middleware/NgrokHeaders.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NgrokHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Skip ngrok browser warning
        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}
```

Register di `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \App\Http\Middleware\NgrokHeaders::class,
    ],
];
```

### CORS Configuration

Jika menggunakan API dari frontend terpisah:

```php
<?php
// config/cors.php

return [
    'paths' => ['api/*', 'camera/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://sensationally-uninflective-porsha.ngrok-free.dev',
        'http://localhost:3000', // Jika ada frontend dev server
    ],
    'allowed_origins_patterns' => ['*.ngrok-free.dev'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Public URL untuk Image Download

ESP32 CAM publish image metadata dengan URL HTTP. Jika Laravel perlu download dari ngrok URL (reverse proxy scenario):

**Option 1: Direct ESP32 HTTP**

```php
// Download langsung dari ESP32 IP (recommended)
$imageUrl = "http://192.168.1.100/capture";
```

**Option 2: Via Ngrok Proxy** (jika ESP32 tidak accessible dari internet)

```php
// Setup proxy route di Laravel
Route::get('/proxy/camera/{deviceId}/capture', function($deviceId) {
    $device = IoTDevice::where('device_id', $deviceId)->firstOrFail();
    $imageData = Http::get("http://{$device->ip}/capture")->body();

    return response($imageData, 200)
        ->header('Content-Type', 'image/jpeg');
});

// ESP32 publish URL via ngrok
"url": "https://sensationally-uninflective-porsha.ngrok-free.dev/proxy/camera/esp32-cam-01/capture"
```

### Testing dengan Ngrok

1. **Start Ngrok**:

   ```bash
   ngrok http 8000 --domain=sensationally-uninflective-porsha.ngrok-free.dev
   ```

2. **Start Laravel**:

   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

3. **Start MQTT Subscriber**:

   ```bash
   php artisan mqtt:camera-subscribe
   ```

4. **Access Dashboard**:
   ```
   https://sensationally-uninflective-porsha.ngrok-free.dev/camera/live
   ```

### Ngrok Free Tier Limitations

⚠️ **Important**: Ngrok free tier memiliki limitations:

- Domain mungkin berubah jika restart (kecuali reserved domain)
- Session timeout setelah beberapa jam inactivity
- Rate limiting untuk requests

**Production**: Gunakan domain permanent atau deploy ke VPS/cloud hosting.

---

## 🔌 MQTT Configuration

### Broker Details

```env
# .env
MQTT_HOST=3ccfdf64ce1f497db3a40c9b37fe1624.s1.eu.hivemq.cloud
MQTT_PORT=8883
MQTT_USERNAME=mizaell
MQTT_PASSWORD=Miegoreng1-
MQTT_TLS=true
MQTT_CLIENT_ID=laravel-backend
```

### Library Installation

```bash
composer require php-mqtt/laravel-client
```

Publish config:

```bash
php artisan vendor:publish --provider="PhpMqtt\Client\MqttClientServiceProvider"
```

Update `config/mqtt-client.php`:

```php
'connections' => [
    'default' => [
        'host' => env('MQTT_HOST'),
        'port' => env('MQTT_PORT', 8883),
        'username' => env('MQTT_USERNAME'),
        'password' => env('MQTT_PASSWORD'),
        'use_tls' => env('MQTT_TLS', true),
        'tls_verify_peer' => false,
        'tls_verify_peer_name' => false,
    ],
],
```

---

## 📡 MQTT Topics

### Topics ESP32 PUBLISH (Laravel Subscribe)

#### 1. Device Status

**Topic**: `iot/devices/esp32-cam-01/status`  
**QoS**: 1  
**Retain**: true

**Payload**:

```json
{
  "device_id": "esp32-cam-01",
  "status": "online",
  "uptime": 123456,
  "rssi": -65,
  "free_heap": 234567,
  "camera_ready": true,
  "fps": 9.8,
  "ip": "192.168.1.100"
}
```

**Update Frequency**: Every 30 seconds

#### 2. Image Metadata

**Topic**: `iot/devices/esp32-cam-01/image`  
**QoS**: 0

**Payload**:

```json
{
  "device_id": "esp32-cam-01",
  "timestamp": 1234567890,
  "format": "jpeg",
  "size": 45678,
  "width": 320,
  "height": 240,
  "url": "http://192.168.1.100/capture"
}
```

**Update**: On demand (via capture command) atau setiap 5 detik jika streaming aktif

### Topics ESP32 SUBSCRIBE (Laravel Publish)

#### Commands Topic

**Topic**: `iot/devices/esp32-cam-01/commands`  
**QoS**: 1

**Commands Available**:

| Command        | Payload                                                  | Description           |
| -------------- | -------------------------------------------------------- | --------------------- |
| Capture        | `{"cmd":"capture"}`                                      | Capture single image  |
| Start Stream   | `{"cmd":"stream_start"}`                                 | Auto-capture every 5s |
| Stop Stream    | `{"cmd":"stream_stop"}`                                  | Stop auto-capture     |
| Flash On       | `{"cmd":"flash_on"}`                                     | Turn on flash LED     |
| Flash Off      | `{"cmd":"flash_off"}`                                    | Turn off flash LED    |
| Set Quality    | `{"cmd":"set_quality","params":{"quality":10}}`          | JPEG quality 0-63     |
| Set Resolution | `{"cmd":"set_resolution","params":{"resolution":"VGA"}}` | Frame size            |

**Resolution Options**: `UXGA`, `SXGA`, `XGA`, `SVGA`, `VGA`, `QVGA`

---

## 🗄️ Database Schema

### Migration: Camera Images Table

```php
<?php
// database/migrations/xxxx_create_camera_images_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camera_images', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index();
            $table->string('filename');
            $table->string('path');
            $table->integer('size');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('format')->default('jpeg');
            $table->text('thumbnail_path')->nullable();
            $table->timestamp('captured_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_images');
    }
};
```

### Migration: Update IoT Devices Table

Jika belum ada kolom `ip` dan `last_image_at`:

```php
<?php
// database/migrations/xxxx_add_camera_fields_to_iot_devices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iot_devices', function (Blueprint $table) {
            $table->string('ip')->nullable()->after('device_id');
            $table->float('fps')->nullable()->after('ip');
            $table->timestamp('last_image_at')->nullable()->after('last_seen');
        });
    }

    public function down(): void
    {
        Schema::table('iot_devices', function (Blueprint $table) {
            $table->dropColumn(['ip', 'fps', 'last_image_at']);
        });
    }
};
```

Run migrations:

```bash
php artisan migrate
```

---

## 📦 Model

### CameraImage Model

```php
<?php
// app/Models/CameraImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraImage extends Model
{
    protected $fillable = [
        'device_id',
        'filename',
        'path',
        'size',
        'width',
        'height',
        'format',
        'thumbnail_path',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IoTDevice::class, 'device_id', 'device_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path
            ? asset('storage/' . $this->thumbnail_path)
            : $this->url;
    }
}
```

### Update IoTDevice Model

Tambahkan relationship:

```php
// app/Models/IoTDevice.php

public function cameraImages()
{
    return $this->hasMany(CameraImage::class, 'device_id', 'device_id');
}

public function latestImage()
{
    return $this->hasOne(CameraImage::class, 'device_id', 'device_id')
                ->latestOfMany('captured_at');
}
```

---

## 🔧 MQTT Subscriber Command

### Create Artisan Command

```bash
php artisan make:command CameraMqttSubscriber
```

### Implementation

```php
<?php
// app/Console/Commands/CameraMqttSubscriber.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Models\IoTDevice;
use App\Models\CameraImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CameraMqttSubscriber extends Command
{
    protected $signature = 'mqtt:camera-subscribe';
    protected $description = 'Subscribe to ESP32 CAM MQTT topics';

    public function handle()
    {
        $this->info('Starting ESP32 CAM MQTT subscriber...');

        $mqtt = MQTT::connection();

        // Subscribe to all camera devices
        $mqtt->subscribe('iot/devices/+/status', function (string $topic, string $message) {
            $this->handleStatusMessage($topic, $message);
        }, 1);

        $mqtt->subscribe('iot/devices/+/image', function (string $topic, string $message) {
            $this->handleImageMessage($topic, $message);
        }, 0);

        $this->info('Subscribed to camera topics. Listening...');

        $mqtt->loop(true);
    }

    private function handleStatusMessage(string $topic, string $message)
    {
        try {
            $data = json_decode($message, true);

            if (!isset($data['device_id'])) {
                return;
            }

            $deviceId = $data['device_id'];

            // Update device status
            IoTDevice::where('device_id', $deviceId)->update([
                'status' => $data['status'] ?? 'unknown',
                'ip' => $data['ip'] ?? null,
                'fps' => $data['fps'] ?? null,
                'last_seen' => now(),
            ]);

            $this->line("[STATUS] {$deviceId}: {$data['status']} | IP: {$data['ip']} | FPS: {$data['fps']}");

            Log::info('Camera status updated', [
                'device_id' => $deviceId,
                'status' => $data['status'],
                'rssi' => $data['rssi'] ?? null,
                'free_heap' => $data['free_heap'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process camera status', [
                'error' => $e->getMessage(),
                'topic' => $topic,
            ]);
        }
    }

    private function handleImageMessage(string $topic, string $message)
    {
        try {
            $data = json_decode($message, true);

            if (!isset($data['device_id'], $data['url'])) {
                return;
            }

            $deviceId = $data['device_id'];
            $imageUrl = $data['url'];

            $this->line("[IMAGE] {$deviceId}: {$data['size']} bytes | {$data['width']}x{$data['height']}");

            // Download image from ESP32 HTTP endpoint
            $imageData = Http::timeout(10)->get($imageUrl)->body();

            if (empty($imageData)) {
                Log::warning('Failed to download image', ['url' => $imageUrl]);
                return;
            }

            // Generate filename
            $filename = $deviceId . '_' . now()->format('YmdHis') . '.jpg';
            $path = 'camera/' . $deviceId . '/' . now()->format('Y/m/d') . '/' . $filename;

            // Store image
            Storage::put($path, $imageData);

            // Save to database
            CameraImage::create([
                'device_id' => $deviceId,
                'filename' => $filename,
                'path' => $path,
                'size' => $data['size'] ?? strlen($imageData),
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'format' => $data['format'] ?? 'jpeg',
                'captured_at' => now(),
            ]);

            // Update device last_image_at
            IoTDevice::where('device_id', $deviceId)->update([
                'last_image_at' => now(),
            ]);

            $this->info("[SAVED] Image saved: {$path}");

        } catch (\Exception $e) {
            Log::error('Failed to process camera image', [
                'error' => $e->getMessage(),
                'topic' => $topic,
            ]);
        }
    }
}
```

### Run Subscriber

Development:

```bash
php artisan mqtt:camera-subscribe
```

Production (with Supervisor):

```ini
# /etc/supervisor/conf.d/laravel-camera-mqtt.conf
[program:laravel-camera-mqtt]
process_name=%(program_name)s
command=php /path/to/artisan mqtt:camera-subscribe
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/camera-mqtt.log
```

---

## 🎮 Camera Controller

### Create Controller

```bash
php artisan make:controller CameraController
```

### Implementation

```php
<?php
// app/Http/Controllers/CameraController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IoTDevice;
use App\Models\CameraImage;
use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Facades\Log;

class CameraController extends Controller
{
    /**
     * Publish command to ESP32 CAM
     */
    private function publishCommand(string $deviceId, string $cmd, array $params = [])
    {
        $topic = "iot/devices/{$deviceId}/commands";

        $payload = ['cmd' => $cmd];
        if (!empty($params)) {
            $payload['params'] = $params;
        }

        try {
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, json_encode($payload), 1);
            $mqtt->disconnect();

            Log::info('Camera command sent', [
                'device_id' => $deviceId,
                'command' => $cmd,
                'params' => $params,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send camera command', [
                'error' => $e->getMessage(),
                'device_id' => $deviceId,
                'command' => $cmd,
            ]);
            return false;
        }
    }

    /**
     * Upload image from ESP32 CAM (HTTP POST)
     * This endpoint receives images directly from ESP32
     */
    public function uploadImage(Request $request)
    {
        // Validate API key
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== env('ESP32_API_KEY', 'supersecretkey123')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        $deviceId = $request->header('X-Device-ID');
        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID required',
            ], 400);
        }

        // Get image data from request body
        $imageData = $request->getContent();

        if (empty($imageData)) {
            return response()->json([
                'success' => false,
                'message' => 'No image data received',
            ], 400);
        }

        try {
            // Generate filename
            $filename = $deviceId . '_' . now()->format('YmdHis') . '.jpg';
            $path = 'camera/' . $deviceId . '/' . now()->format('Y/m/d') . '/' . $filename;

            // Store image
            Storage::put($path, $imageData);

            // Get image dimensions (optional, requires GD)
            $width = null;
            $height = null;
            try {
                $tempPath = storage_path('app/' . $path);
                if (file_exists($tempPath)) {
                    list($width, $height) = getimagesize($tempPath);
                }
            } catch (\Exception $e) {
                // Ignore dimension errors
            }

            // Save to database
            $image = CameraImage::create([
                'device_id' => $deviceId,
                'filename' => $filename,
                'path' => $path,
                'size' => strlen($imageData),
                'width' => $width,
                'height' => $height,
                'format' => 'jpeg',
                'captured_at' => now(),
            ]);

            // Update device last_image_at
            IoTDevice::where('device_id', $deviceId)->update([
                'last_image_at' => now(),
            ]);

            Log::info('Camera image uploaded', [
                'device_id' => $deviceId,
                'size' => strlen($imageData),
                'path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'size' => strlen($imageData),
                    'url' => asset('storage/' . $path),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to upload camera image', [
                'error' => $e->getMessage(),
                'device_id' => $deviceId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Capture single image
     */
    public function capture(Request $request, string $deviceId)
    {
        $success = $this->publishCommand($deviceId, 'capture');

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Capture command sent' : 'Failed to send command',
        ]);
    }

    /**
     * Start streaming (auto-capture every 5s)
     */
    public function startStream(Request $request, string $deviceId)
    {
        $success = $this->publishCommand($deviceId, 'stream_start');

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Streaming started' : 'Failed to start streaming',
        ]);
    }

    /**
     * Stop streaming
     */
    public function stopStream(Request $request, string $deviceId)
    {
        $success = $this->publishCommand($deviceId, 'stream_stop');

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Streaming stopped' : 'Failed to stop streaming',
        ]);
    }

    /**
     * Control flash LED
     */
    public function flashControl(Request $request, string $deviceId)
    {
        $request->validate([
            'state' => 'required|in:on,off',
        ]);

        $cmd = $request->state === 'on' ? 'flash_on' : 'flash_off';
        $success = $this->publishCommand($deviceId, $cmd);

        return response()->json([
            'success' => $success,
            'message' => $success ? "Flash {$request->state}" : 'Failed to control flash',
        ]);
    }

    /**
     * Set image quality
     */
    public function setQuality(Request $request, string $deviceId)
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:63',
        ]);

        $success = $this->publishCommand($deviceId, 'set_quality', [
            'quality' => (int) $request->quality,
        ]);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Quality updated' : 'Failed to set quality',
        ]);
    }

    /**
     * Set resolution
     */
    public function setResolution(Request $request, string $deviceId)
    {
        $request->validate([
            'resolution' => 'required|in:UXGA,SXGA,XGA,SVGA,VGA,QVGA',
        ]);

        $success = $this->publishCommand($deviceId, 'set_resolution', [
            'resolution' => $request->resolution,
        ]);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Resolution updated' : 'Failed to set resolution',
        ]);
    }

    /**
     * Get camera images gallery
     */
    public function gallery(Request $request, string $deviceId)
    {
        $images = CameraImage::where('device_id', $deviceId)
            ->orderBy('captured_at', 'desc')
            ->paginate(20);

        return view('camera.gallery', compact('images', 'deviceId'));
    }

    /**
     * Get latest image
     */
    public function latest(string $deviceId)
    {
        $image = CameraImage::where('device_id', $deviceId)
            ->latest('captured_at')
            ->first();

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'No images found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'image' => [
                'url' => $image->url,
                'thumbnail' => $image->thumbnail_url,
                'size' => $image->size,
                'width' => $image->width,
                'height' => $image->height,
                'captured_at' => $image->captured_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Live camera view
     */
    public function live(Request $request)
    {
        $cameras = IoTDevice::where('type', 'camera')
            ->orWhere('device_id', 'like', '%cam%')
            ->with('latestImage')
            ->get();

        $selectedDeviceId = $request->get('device_id', $cameras->first()?->device_id);
        $selectedCamera = $cameras->firstWhere('device_id', $selectedDeviceId);

        return view('camera.live', compact('cameras', 'selectedCamera'));
    }
}
```

---

## 🛣️ Routes

```php
<?php
// routes/web.php

use App\Http\Controllers\CameraController;

Route::middleware(['auth'])->prefix('camera')->name('camera.')->group(function () {
    // Live view
    Route::get('/live', [CameraController::class, 'live'])->name('live');

    // Gallery
    Route::get('/{deviceId}/gallery', [CameraController::class, 'gallery'])->name('gallery');

    // Commands
    Route::post('/{deviceId}/capture', [CameraController::class, 'capture'])->name('capture');
    Route::post('/{deviceId}/stream/start', [CameraController::class, 'startStream'])->name('stream.start');
    Route::post('/{deviceId}/stream/stop', [CameraController::class, 'stopStream'])->name('stream.stop');
    Route::post('/{deviceId}/flash', [CameraController::class, 'flashControl'])->name('flash');
    Route::post('/{deviceId}/quality', [CameraController::class, 'setQuality'])->name('quality');
    Route::post('/{deviceId}/resolution', [CameraController::class, 'setResolution'])->name('resolution');

    // API
    Route::get('/{deviceId}/latest', [CameraController::class, 'latest'])->name('latest');
});

// routes/api.php - Image Upload from ESP32
Route::post('/camera/upload', [CameraController::class, 'uploadImage']);
```

> [!IMPORTANT] > **CSRF Exception Required**: Add `/api/camera/upload` to CSRF exemption karena ESP32 tidak bisa kirim CSRF token.

Update `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    'api/*',
];
```

---

## 🚀 Production Deployment dengan HTTP Upload

### Overview

Untuk deployment production dimana Laravel di-hosting di cloud server (VPS/shared hosting), ESP32 CAM sekarang **upload images via HTTP POST** langsung ke Laravel.

### Architecture Flow

```
ESP32 CAM (Home Network)
    ├─ MQTT → HiveMQ Cloud → Laravel ✅ (Commands & Status)
    └─ HTTP POST → Ngrok/VPS → Laravel ✅ (Image Upload)
```

### ESP32 Configuration

ESP32 sudah dikonfigurasi untuk upload ke Laravel:

```cpp
// Current ngrok URL (Development)
const char *LARAVEL_SERVER_URL = "https://sensationally-uninflective-porsha.ngrok-free.dev/api/camera/upload";
const char *LARAVEL_API_KEY = "supersecretkey123";

// For Production, update to:
// const char *LARAVEL_SERVER_URL = "https://yourdomain.com/api/camera/upload";
```

### How It Works

1. **Command Received**: ESP32 receives `capture` command via MQTT
2. **Capture Image**: ESP32 captures image with camera
3. **Upload to Laravel**: ESP32 POSTs image via HTTPS to Laravel API
4. **Store in Database**: Laravel saves image to storage and database
5. **Publish Metadata**: ESP32 publishes metadata via MQTT dengan URL Laravel

**Image Metadata JSON**:

```json
{
  "device_id": "esp32-cam-01",
  "timestamp": 1234567890,
  "format": "jpeg",
  "size": 45678,
  "width": 640,
  "height": 480,
  "url": "https://yourdomain.com/camera/latest/esp32-cam-01",
  "uploaded": true
}
```

### Laravel API Endpoint

**Endpoint**: `POST /api/camera/upload`

**Headers**:

- `Content-Type: image/jpeg`
- `X-Device-ID: esp32-cam-01`
- `X-API-KEY: supersecretkey123`

**Body**: Raw JPEG image data

**Response** (201 Created):

```json
{
  "success": true,
  "message": "Image uploaded successfully",
  "data": {
    "filename": "esp32-cam-01_20251216133045.jpg",
    "size": 45678,
    "url": "https://yourdomain.com/storage/camera/esp32-cam-01/2025/12/16/esp32-cam-01_20251216133045.jpg"
  }
}
```

### Configuration Steps

#### 1. Environment Variables

Add to `.env`:

```env
ESP32_API_KEY=supersecretkey123
```

#### 2. Storage Link

```bash
php artisan storage:link
```

#### 3. MQTT Subscriber Update

MQTT subscriber tidak perlu download image lagi (ESP32 sudah upload), tapi tetap handle metadata:

```php
private function handleImageMessage(string $topic, string $message)
{
    $data = json_decode($message, true);

    // Just log metadata, image already uploaded
    Log::info('Camera image uploaded via HTTP', [
        'device_id' => $data['device_id'],
        'url' => $data['url'],
        'uploaded' => $data['uploaded'] ?? false,
    ]);

    // Update device timestamp
    IoTDevice::where('device_id', $data['device_id'])->update([
        'last_image_at' => now(),
    ]);
}
```

### Benefits

✅ **Works in Production**: ESP32 di home network, Laravel di cloud  
✅ **No VPN Required**: Direct HTTPS upload  
✅ **Secure**: API key authentication  
✅ **Reliable**: HTTP POST dengan retry capability  
✅ **Scalable**: Images stored di cloud storage

---

## 🧪 Testing

### 1. Test MQTT Subscriber

```bash
# Terminal 1: Run subscriber
php artisan mqtt:camera-subscribe

# Terminal 2: Check logs
tail -f storage/logs/laravel.log
```

Expected output saat ESP32 running:

```
[STATUS] esp32-cam-01: online | IP: 192.168.1.100 | FPS: 9.8
```

### 2. Test Capture Command

```bash
php artisan tinker
```

```php
// Send capture command
$mqtt = \PhpMqtt\Client\Facades\MQTT::connection();
$mqtt->publish('iot/devices/esp32-cam-01/commands', '{"cmd":"capture"}', 1);
$mqtt->disconnect();

// Check if image saved
\App\Models\CameraImage::latest()->first();
```

### 3. Test API Endpoints

```bash
# Capture
curl -X POST http://localhost:8000/camera/esp32-cam-01/capture

# Flash ON
curl -X POST http://localhost:8000/camera/esp32-cam-01/flash \
  -H "Content-Type: application/json" \
  -d '{"state":"on"}'

# Set Quality
curl -X POST http://localhost:8000/camera/esp32-cam-01/quality \
  -H "Content-Type: application/json" \
  -d '{"quality":10}'

# Get Latest Image
curl http://localhost:8000/camera/esp32-cam-01/latest
```

---

## 🎨 Frontend Example (Blade)

### Live Camera View

```blade
{{-- resources/views/camera/live.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Live Camera</h1>

    <!-- Camera Selection -->
    <div class="mb-3">
        <select id="cameraSelect" class="form-select" onchange="window.location.href='?device_id='+this.value">
            @foreach($cameras as $camera)
                <option value="{{ $camera->device_id }}"
                    {{ $selectedCamera && $selectedCamera->device_id === $camera->device_id ? 'selected' : '' }}>
                    {{ $camera->name ?? $camera->device_id }}
                </option>
            @endforeach
        </select>
    </div>

    @if($selectedCamera)
        <div class="row">
            <div class="col-md-8">
                <!-- Live Image -->
                <div class="card">
                    <div class="card-body">
                        <img id="cameraImage"
                             src="{{ $selectedCamera->latestImage?->url ?? asset('images/no-camera.png') }}"
                             alt="Camera Feed"
                             class="img-fluid">
                        <div class="mt-2">
                            <span class="badge bg-secondary" id="statusBadge">Offline</span>
                            <small class="text-muted ms-2">IP: {{ $selectedCamera->ip ?? 'N/A' }}</small>
                            <small class="text-muted ms-2">FPS: <span id="fpsBadge">{{ $selectedCamera->fps ?? '0' }}</span></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Controls -->
                <div class="card">
                    <div class="card-header">Camera Controls</div>
                    <div class="card-body">
                        <button class="btn btn-primary w-100 mb-2" onclick="sendCommand('capture')">
                            📸 Capture
                        </button>
                        <button class="btn btn-success w-100 mb-2" onclick="sendCommand('stream_start')">
                            ▶️ Start Stream
                        </button>
                        <button class="btn btn-danger w-100 mb-2" onclick="sendCommand('stream_stop')">
                            ⏹️ Stop Stream
                        </button>
                        <hr>
                        <button class="btn btn-warning w-100 mb-2" onclick="sendCommand('flash_on')">
                            💡 Flash ON
                        </button>
                        <button class="btn btn-secondary w-100 mb-2" onclick="sendCommand('flash_off')">
                            Flash OFF
                        </button>
                        <hr>
                        <div class="mb-2">
                            <label>Quality (0-63)</label>
                            <input type="number" id="qualityInput" class="form-control" value="10" min="0" max="63">
                            <button class="btn btn-sm btn-primary mt-1" onclick="setQuality()">Set Quality</button>
                        </div>
                        <div>
                            <label>Resolution</label>
                            <select id="resolutionSelect" class="form-select">
                                <option value="QVGA">QVGA (320x240)</option>
                                <option value="VGA" selected>VGA (640x480)</option>
                                <option value="SVGA">SVGA (800x600)</option>
                                <option value="XGA">XGA (1024x768)</option>
                            </select>
                            <button class="btn btn-sm btn-primary mt-1" onclick="setResolution()">Set Resolution</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">No camera devices found</div>
    @endif
</div>

<script>
const deviceId = '{{ $selectedCamera?->device_id }}';

function sendCommand(cmd) {
    let url = `/camera/${deviceId}/`;

    if (cmd === 'capture') url += 'capture';
    else if (cmd === 'stream_start') url += 'stream/start';
    else if (cmd === 'stream_stop') url += 'stream/stop';
    else if (cmd === 'flash_on' || cmd === 'flash_off') {
        url += 'flash';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ state: cmd === 'flash_on' ? 'on' : 'off' })
        }).then(res => res.json()).then(data => {
            alert(data.message);
        });
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).then(res => res.json()).then(data => {
        alert(data.message);
    });
}

function setQuality() {
    const quality = document.getElementById('qualityInput').value;
    fetch(`/camera/${deviceId}/quality`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ quality: parseInt(quality) })
    }).then(res => res.json()).then(data => {
        alert(data.message);
    });
}

function setResolution() {
    const resolution = document.getElementById('resolutionSelect').value;
    fetch(`/camera/${deviceId}/resolution`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ resolution: resolution })
    }).then(res => res.json()).then(data => {
        alert(data.message);
    });
}

// Auto-refresh image every 5 seconds
setInterval(() => {
    fetch(`/camera/${deviceId}/latest`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('cameraImage').src = data.image.url + '?t=' + new Date().getTime();
            }
        });
}, 5000);
</script>
@endsection
```

---

## 📋 Checklist Implementation

### Backend Setup

- [ ] Install `php-mqtt/laravel-client` package
- [ ] Configure MQTT connection in `.env`
- [ ] Run database migrations
- [ ] Create `CameraImage` model
- [ ] Update `IoTDevice` model with relationships
- [ ] Test MQTT connection

### MQTT Integration

- [ ] Create `CameraMqttSubscriber` command
- [ ] Implement status message handler
- [ ] Implement image message handler
- [ ] Test subscriber with ESP32 running
- [ ] Setup Supervisor for production

### API Development

- [ ] Create `CameraController`
- [ ] Implement all command methods
- [ ] Add routes to `web.php`
- [ ] Test API endpoints via Postman/curl
- [ ] Add CSRF protection

### Frontend

- [ ] Create live camera view
- [ ] Add camera controls UI
- [ ] Implement auto-refresh for latest image
- [ ] Create gallery view for image history
- [ ] Add responsive design

### Testing

- [ ] Test capture command
- [ ] Test streaming start/stop
- [ ] Test flash control
- [ ] Test quality/resolution settings
- [ ] Verify images saved to storage
- [ ] Check database records

---

## 🚀 Deployment Tips

### Production Considerations

1. **Storage**: Ensure `storage/app/camera` has write permissions
2. **Symbolic Link**: Run `php artisan storage:link`
3. **Supervisor**: Monitor MQTT subscriber process
4. **Logs**: Monitor `storage/logs/camera-mqtt.log`
5. **Cleanup**: Implement cron job to delete old images
6. **TLS**: Use proper certificate validation in production

### Performance Optimization

1. **Queue Image Processing**: Use Laravel queues for heavy tasks
2. **Thumbnail Generation**: Create thumbnails asynchronously
3. **Cache**: Cache latest image to reduce database queries
4. **CDN**: Serve images via CDN for better performance

---

**Document Version**: 1.0  
**Author**: ESP32 CAM Integration Team  
**Support**: Check `walkthrough.md` for ESP32 firmware testing
