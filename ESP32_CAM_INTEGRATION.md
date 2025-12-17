# 📸 ESP32 CAM Integration Documentation

**Cat Cage Monitoring System - Camera Module**

---

## 1️⃣ **Overview**

### Tujuan Integrasi

ESP32 CAM diintegrasikan ke dalam sistem monitoring sangkar kucing untuk:

-   **Live Streaming**: Monitor kondisi sangkar secara real-time
-   **Snapshot Capture**: Ambil foto untuk dokumentasi atau deteksi aktivitas
-   **Remote Monitoring**: Akses camera dari dashboard web

### Arsitektur Sistem

```
ESP32 CAM ──[Wi-Fi]──> MQTT Broker ──> Laravel Backend ──> Web UI
                            │
                            └──> Image Storage (Local/Cloud)
```

---

## 2️⃣ **Hardware Requirements**

### ESP32 CAM Board

-   **Model**: ESP32-CAM (AI-Thinker)
-   **Camera**: OV2640 (2MP)
-   **RAM**: 520 KB SRAM + 4 MB PSRAM
-   **Flash**: Minimum 4 MB
-   **Power**: 5V via USB atau regulator

### Pin Configuration

| Function     | Pin                               | Notes                |
| ------------ | --------------------------------- | -------------------- |
| Camera D0-D7 | GPIO5, 18, 19, 21, 36, 39, 34, 35 | OV2640 data          |
| Camera XCLK  | GPIO0                             | Camera clock         |
| Camera PCLK  | GPIO22                            | Pixel clock          |
| Camera VSYNC | GPIO25                            | Vertical sync        |
| Camera HREF  | GPIO23                            | Horizontal reference |
| Camera SDA   | GPIO26                            | I2C data             |
| Camera SCL   | GPIO27                            | I2C clock            |
| Camera RESET | GPIO32                            | Camera reset         |
| Camera PWDN  | GPIO32                            | Power down           |
| Flash LED    | GPIO4                             | Built-in flash       |

### Tambahan Hardware

-   **FTDI Programmer**: Untuk upload firmware (atau gunakan USB-to-Serial)
-   **Power Supply**: 5V 2A minimum
-   **Casing**: Housing tahan air (opsional)

---

## 3️⃣ **Firmware ESP32 CAM**

### Library yang Dibutuhkan

```cpp
#include <WiFi.h>
#include <PubSubClient.h>
#include "esp_camera.h"
#include "esp_timer.h"
#include "img_converters.h"
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"
#include <ArduinoJson.h>
```

### Install via Arduino IDE

```
1. File > Preferences > Additional Board Manager URLs:
   https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json

2. Tools > Board > Boards Manager > Search "esp32" > Install

3. Sketch > Include Library > Manage Libraries:
   - PubSubClient by Nick O'Leary
   - ArduinoJson by Benoit Blanchon
```

### Konfigurasi WiFi dan MQTT

```cpp
// WiFi Configuration
const char* WIFI_SSID = "Ell";  // Sesuaikan dengan WiFi Anda
const char* WIFI_PASSWORD = "Mizaell14-";

// MQTT Configuration (HiveMQ Cloud)
const char* MQTT_BROKER = "3ccfdf64ce1f497db3a40c9b37fe1624.s1.eu.hivemq.cloud";
const int MQTT_PORT = 8883;  // TLS/SSL port
const char* MQTT_USERNAME = "mizaell";
const char* MQTT_PASSWORD = "Miegoreng1-";
const char* DEVICE_ID = "esp32-cam-01";  // Unique ID untuk camera

// MQTT Topics
String TOPIC_IMAGE = "iot/devices/" + String(DEVICE_ID) + "/image";
String TOPIC_STATUS = "iot/devices/" + String(DEVICE_ID) + "/status";
String TOPIC_COMMANDS = "iot/devices/" + String(DEVICE_ID) + "/commands";
```

### Camera Pin Definition (AI-Thinker)

```cpp
#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27

#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5
#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22
```

### Camera Initialization

```cpp
void setupCamera() {
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  // Frame size settings
  if(psramFound()){
    config.frame_size = FRAMESIZE_UXGA;  // 1600x1200
    config.jpeg_quality = 10;  // 0-63, lower is better quality
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_SVGA;  // 800x600
    config.jpeg_quality = 12;
    config.fb_count = 1;
  }

  // Initialize camera
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    return;
  }

  // Optional: Adjust sensor settings
  sensor_t * s = esp_camera_sensor_get();
  s->set_brightness(s, 0);     // -2 to 2
  s->set_contrast(s, 0);       // -2 to 2
  s->set_saturation(s, 0);     // -2 to 2
  s->set_special_effect(s, 0); // 0-6
  s->set_whitebal(s, 1);       // 0-1
  s->set_awb_gain(s, 1);       // 0-1
  s->set_wb_mode(s, 0);        // 0-4
  s->set_exposure_ctrl(s, 1);  // 0-1
  s->set_aec2(s, 0);           // 0-1
  s->set_ae_level(s, 0);       // -2 to 2
  s->set_aec_value(s, 300);    // 0-1200
  s->set_gain_ctrl(s, 1);      // 0-1
  s->set_agc_gain(s, 0);       // 0-30
  s->set_gainceiling(s, (gainceiling_t)0);  // 0-6
  s->set_bpc(s, 0);            // 0-1
  s->set_wpc(s, 1);            // 0-1
  s->set_raw_gma(s, 1);        // 0-1
  s->set_lenc(s, 1);           // 0-1
  s->set_hmirror(s, 0);        // 0-1
  s->set_vflip(s, 0);          // 0-1
  s->set_dcw(s, 1);            // 0-1
  s->set_colorbar(s, 0);       // 0-1
}
```

### Capture and Publish Image

```cpp
void captureAndPublishImage() {
  // Capture image
  camera_fb_t * fb = esp_camera_fb_get();
  if(!fb) {
    Serial.println("Camera capture failed");
    return;
  }

  Serial.printf("Image captured: %d bytes\n", fb->len);

  // Option 1: Publish as Base64 (untuk image kecil)
  String base64Image = base64::encode(fb->buf, fb->len);

  StaticJsonDocument<512> doc;
  doc["device_id"] = DEVICE_ID;
  doc["timestamp"] = millis();
  doc["format"] = "jpeg";
  doc["size"] = fb->len;
  doc["width"] = fb->width;
  doc["height"] = fb->height;
  doc["image"] = base64Image;  // WARNING: Bisa terlalu besar untuk MQTT!

  String payload;
  serializeJson(doc, payload);

  mqttClient.publish(TOPIC_IMAGE.c_str(), payload.c_str());

  // Option 2: Publish metadata only, image via HTTP
  // (Recommended untuk image besar)

  // Return frame buffer
  esp_camera_fb_return(fb);
}
```

### MQTT Command Handler

```cpp
void mqttCallback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (unsigned int i = 0; i < length; i++) {
    message += (char)payload[i];
  }

  StaticJsonDocument<512> doc;
  DeserializationError error = deserializeJson(doc, message);

  if (error) {
    Serial.println("JSON parse failed");
    return;
  }

  String cmd = doc["cmd"];

  if (cmd == "capture") {
    // Capture single image
    captureAndPublishImage();
  }
  else if (cmd == "stream_start") {
    // Start streaming
    streamingEnabled = true;
  }
  else if (cmd == "stream_stop") {
    // Stop streaming
    streamingEnabled = false;
  }
  else if (cmd == "flash_on") {
    // Turn on flash LED
    digitalWrite(4, HIGH);
  }
  else if (cmd == "flash_off") {
    // Turn off flash LED
    digitalWrite(4, LOW);
  }
  else if (cmd == "set_quality") {
    // Change JPEG quality
    int quality = doc["params"]["quality"];
    sensor_t * s = esp_camera_sensor_get();
    s->set_quality(s, quality);
  }
  else if (cmd == "set_resolution") {
    // Change frame size
    String res = doc["params"]["resolution"];
    framesize_t frameSize = FRAMESIZE_VGA;

    if (res == "UXGA") frameSize = FRAMESIZE_UXGA;
    else if (res == "SXGA") frameSize = FRAMESIZE_SXGA;
    else if (res == "XGA") frameSize = FRAMESIZE_XGA;
    else if (res == "SVGA") frameSize = FRAMESIZE_SVGA;
    else if (res == "VGA") frameSize = FRAMESIZE_VGA;

    sensor_t * s = esp_camera_sensor_get();
    s->set_framesize(s, frameSize);
  }
}
```

### Complete Arduino Sketch Structure

```cpp
#include <WiFi.h>
#include <PubSubClient.h>
#include <WiFiClientSecure.h>
#include "esp_camera.h"
#include <ArduinoJson.h>

// Configuration (see above)
// ...

WiFiClientSecure wifiClient;
PubSubClient mqttClient(wifiClient);
bool streamingEnabled = false;

void setup() {
  Serial.begin(115200);

  // Disable brownout detector
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);

  // Setup flash LED
  pinMode(4, OUTPUT);
  digitalWrite(4, LOW);

  // Setup camera
  setupCamera();

  // Connect WiFi
  connectWiFi();

  // Setup MQTT
  wifiClient.setInsecure();  // For HiveMQ Cloud
  mqttClient.setServer(MQTT_BROKER, MQTT_PORT);
  mqttClient.setCallback(mqttCallback);
  mqttClient.setBufferSize(50000);  // Increase for images

  connectMQTT();
}

void loop() {
  if (!mqttClient.connected()) {
    connectMQTT();
  }
  mqttClient.loop();

  // Auto-capture every 10 seconds if streaming enabled
  if (streamingEnabled) {
    static unsigned long lastCapture = 0;
    if (millis() - lastCapture > 10000) {
      captureAndPublishImage();
      lastCapture = millis();
    }
  }
}

void connectWiFi() {
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");
  Serial.println(WiFi.localIP());
}

void connectMQTT() {
  while (!mqttClient.connected()) {
    Serial.print("Connecting to MQTT...");
    String clientId = "ESP32CAM-" + String(random(0xffff), HEX);

    if (mqttClient.connect(clientId.c_str(), MQTT_USERNAME, MQTT_PASSWORD)) {
      Serial.println("connected");
      mqttClient.subscribe(TOPIC_COMMANDS.c_str());
      publishStatus("online");
    } else {
      Serial.print("failed, rc=");
      Serial.println(mqttClient.state());
      delay(5000);
    }
  }
}

void publishStatus(String status) {
  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["status"] = status;
  doc["uptime"] = millis();
  doc["rssi"] = WiFi.RSSI();
  doc["free_heap"] = ESP.getFreeHeap();

  String payload;
  serializeJson(doc, payload);
  mqttClient.publish(TOPIC_STATUS.c_str(), payload.c_str(), true);
}
```

---

## 4️⃣ **MQTT Topics untuk Camera**

### Topics yang ESP32 CAM PUBLISH

#### 1. Status Topic

```
iot/devices/esp32-cam-01/status
```

**Payload (Online)**:

```json
{
    "device_id": "esp32-cam-01",
    "status": "online",
    "uptime": 123456,
    "rssi": -65,
    "free_heap": 234567,
    "camera_ready": true
}
```

#### 2. Image Topic

```
iot/devices/esp32-cam-01/image
```

**Payload (Metadata Only)**:

```json
{
    "device_id": "esp32-cam-01",
    "timestamp": "1234567890",
    "format": "jpeg",
    "size": 45678,
    "width": 1600,
    "height": 1200,
    "url": "http://192.168.1.100/image/latest.jpg"
}
```

**Payload (Base64 - Small Images)**:

```json
{
    "device_id": "esp32-cam-01",
    "timestamp": "1234567890",
    "format": "jpeg",
    "size": 12345,
    "width": 640,
    "height": 480,
    "image": "/9j/4AAQSkZJRgABAQEAYABgAAD..."
}
```

### Topics yang ESP32 CAM SUBSCRIBE

#### 1. Commands Topic

```
iot/devices/esp32-cam-01/commands
```

**Command List**:

**Capture Single Image**:

```json
{
    "id": 1,
    "cmd": "capture"
}
```

**Start Streaming**:

```json
{
    "id": 2,
    "cmd": "stream_start",
    "params": {
        "interval": 5000
    }
}
```

**Stop Streaming**:

```json
{
    "id": 3,
    "cmd": "stream_stop"
}
```

**Flash Control**:

```json
{
    "id": 4,
    "cmd": "flash_on"
}
```

```json
{
    "id": 5,
    "cmd": "flash_off"
}
```

**Set Resolution**:

```json
{
    "id": 6,
    "cmd": "set_resolution",
    "params": {
        "resolution": "VGA"
    }
}
```

Options: `UXGA` (1600x1200), `SXGA` (1280x1024), `XGA` (1024x768), `SVGA` (800x600), `VGA` (640x480)

**Set Quality**:

```json
{
    "id": 7,
    "cmd": "set_quality",
    "params": {
        "quality": 10
    }
}
```

Range: 0-63 (lower = better quality, larger file)

---

## 5️⃣ **Backend Laravel Integration**

### Database Migration

```php
// database/migrations/xxxx_create_camera_images_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camera_images', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('filename');
            $table->string('path');
            $table->integer('size');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('format')->default('jpeg');
            $table->text('thumbnail_path')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index('device_id');
            $table->index('captured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_images');
    }
};
```

### Model

```php
// app/Models/CameraImage.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function device()
    {
        return $this->belongsTo(IoTDevice::class, 'device_id', 'device_id');
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path
            ? asset('storage/' . $this->thumbnail_path)
            : $this->url;
    }
}
```

### MQTT Subscriber untuk Image

```php
// app/Console/Commands/CameraMqttSubscriber.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Models\CameraImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CameraMqttSubscriber extends Command
{
    protected $signature = 'mqtt:camera-subscribe';
    protected $description = 'Subscribe to camera MQTT topics';

    public function handle()
    {
        $this->info('Starting camera MQTT subscriber...');

        $mqtt = MQTT::connection();

        // Subscribe to camera image topic
        $mqtt->subscribe('iot/devices/+/image', function (string $topic, string $message) {
            $this->handleImageMessage($topic, $message);
        }, 0);

        $mqtt->loop(true);
    }

    private function handleImageMessage(string $topic, string $message)
    {
        try {
            $data = json_decode($message, true);

            if (!isset($data['device_id'])) {
                return;
            }

            $deviceId = $data['device_id'];

            // Check if image is Base64 encoded
            if (isset($data['image'])) {
                $this->saveBase64Image($data);
            }
            // Or if it's metadata with URL
            elseif (isset($data['url'])) {
                $this->saveImageFromUrl($data);
            }

            Log::info('Camera image received', ['device_id' => $deviceId]);

        } catch (\Exception $e) {
            Log::error('Failed to process camera image', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function saveBase64Image(array $data)
    {
        $imageData = base64_decode($data['image']);
        $filename = $data['device_id'] . '_' . time() . '.jpg';
        $path = 'camera/' . $data['device_id'] . '/' . date('Y/m/d') . '/' . $filename;

        Storage::put($path, $imageData);

        CameraImage::create([
            'device_id' => $data['device_id'],
            'filename' => $filename,
            'path' => $path,
            'size' => $data['size'] ?? strlen($imageData),
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'format' => $data['format'] ?? 'jpeg',
            'captured_at' => now(),
        ]);
    }

    private function saveImageFromUrl(array $data)
    {
        // Download image from ESP32 HTTP server
        // Implementation depends on your setup
    }
}
```

### Camera Controller Methods

```php
// app/Http/Controllers/CameraController.php

public function capture(Request $request, string $deviceId)
{
    $command = [
        'id' => rand(1, 9999),
        'cmd' => 'capture',
    ];

    $success = MqttService::publishESP32Command($deviceId, 'capture');

    return response()->json([
        'success' => $success,
        'message' => $success ? 'Capture command sent' : 'Failed to send command'
    ]);
}

public function startStream(Request $request, string $deviceId)
{
    $interval = $request->input('interval', 5000);

    $success = MqttService::publishESP32Command($deviceId, 'stream_start', [
        'interval' => $interval
    ]);

    return response()->json([
        'success' => $success
    ]);
}

public function stopStream(Request $request, string $deviceId)
{
    $success = MqttService::publishESP32Command($deviceId, 'stream_stop');

    return response()->json([
        'success' => $success
    ]);
}

public function getImages(Request $request, string $deviceId)
{
    $images = CameraImage::where('device_id', $deviceId)
        ->orderBy('captured_at', 'desc')
        ->paginate(20);

    return view('camera.gallery', compact('images', 'deviceId'));
}
```

### Routes

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {
    Route::get('/camera/live', [CameraController::class, 'live'])->name('camera.live');
    Route::get('/camera/{deviceId}/gallery', [CameraController::class, 'getImages'])->name('camera.gallery');
    Route::post('/camera/{deviceId}/capture', [CameraController::class, 'capture'])->name('camera.capture');
    Route::post('/camera/{deviceId}/stream/start', [CameraController::class, 'startStream'])->name('camera.stream.start');
    Route::post('/camera/{deviceId}/stream/stop', [CameraController::class, 'stopStream'])->name('camera.stream.stop');
});
```

---

## 6️⃣ **Frontend Implementation**

### Live Camera View

```blade
{{-- resources/views/camera/live.blade.php --}}
@extends('components.layout')

@section('title', 'Live Camera')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Camera Live Stream</h1>

    <!-- Camera Selection -->
    <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Select Camera</label>
        <select id="cameraSelect" class="px-4 py-2 border rounded-lg">
            @foreach($cameras as $camera)
                <option value="{{ $camera->device_id }}"
                    {{ $selectedCamera && $selectedCamera->deviceId === $camera->device_id ? 'selected' : '' }}>
                    {{ $camera->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Camera Display -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="relative">
            <img id="cameraStream"
                 src="{{ $selectedCamera ? $selectedCamera->latestImageUrl : asset('images/no-camera.png') }}"
                 alt="Camera Stream"
                 class="w-full h-auto rounded-lg"
                 style="max-height: 600px; object-fit: contain;">

            <div id="streamStatus" class="absolute top-4 right-4 px-3 py-1 rounded-full text-sm font-medium bg-gray-500 text-white">
                Offline
            </div>
        </div>

        <!-- Controls -->
        <div class="mt-4 flex gap-2">
            <button id="btnCapture" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                📸 Capture
            </button>
            <button id="btnStreamStart" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                ▶️ Start Stream
            </button>
            <button id="btnStreamStop" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                ⏹️ Stop Stream
            </button>
            <button id="btnFlashOn" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                💡 Flash On
            </button>
            <button id="btnFlashOff" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Flash Off
            </button>
        </div>
    </div>
</div>

<script>
const deviceId = '{{ $selectedCamera ? $selectedCamera->deviceId : "" }}';

// Capture single image
document.getElementById('btnCapture').addEventListener('click', () => {
    fetch(`/camera/${deviceId}/capture`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Capture command sent!');
            }
        });
});

// Start stream
document.getElementById('btnStreamStart').addEventListener('click', () => {
    fetch(`/camera/${deviceId}/stream/start`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ interval: 5000 })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('streamStatus').textContent = 'Streaming';
            document.getElementById('streamStatus').className = 'absolute top-4 right-4 px-3 py-1 rounded-full text-sm font-medium bg-green-500 text-white';
        }
    });
});

// Stop stream
document.getElementById('btnStreamStop').addEventListener('click', () => {
    fetch(`/camera/${deviceId}/stream/stop`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('streamStatus').textContent = 'Offline';
                document.getElementById('streamStatus').className = 'absolute top-4 right-4 px-3 py-1 rounded-full text-sm font-medium bg-gray-500 text-white';
            }
        });
});

// Auto-refresh image (polling)
setInterval(() => {
    const img = document.getElementById('cameraStream');
    img.src = img.src.split('?')[0] + '?t=' + new Date().getTime();
}, 5000);
</script>
@endsection
```

---

## 7️⃣ **Setup dan Konfigurasi**

### Step 1: Setup ESP32 CAM Hardware

1. Pasang ESP32 CAM ke FTDI programmer
2. Hubungkan:
    - ESP32 GND → FTDI GND
    - ESP32 5V → FTDI 5V
    - ESP32 U0R → FTDI TX
    - ESP32 U0T → FTDI RX
    - ESP32 IO0 → GND (untuk programming mode)

### Step 2: Upload Firmware

1. Buka Arduino IDE
2. Tools → Board → ESP32 Dev Module
3. Tools → Port → Pilih COM port FTDI
4. Sketch → Upload
5. Lepas IO0 dari GND setelah upload
6. Reset ESP32

### Step 3: Konfigurasi Laravel

```bash
# Install dependencies
composer require php-mqtt/client

# Run migration
php artisan migrate

# Create storage link
php artisan storage:link

# Start MQTT subscriber
php artisan mqtt:camera-subscribe
```

### Step 4: Testing

```bash
# Terminal 1: Run Laravel
php artisan serve

# Terminal 2: Run MQTT subscriber
php artisan mqtt:camera-subscribe

# Terminal 3: Monitor logs
tail -f storage/logs/laravel.log
```

---

## 8️⃣ **Troubleshooting**

### Camera Init Failed

-   **Solusi**: Periksa pin configuration
-   Pastikan PSRAM enabled (Tools → PSRAM → Enabled)
-   Brownout detector disabled

### Image Too Large for MQTT

-   **Masalah**: MQTT buffer overflow
-   **Solusi**:
    -   Kurangi quality: `config.jpeg_quality = 15-20`
    -   Kurangi resolution: `config.frame_size = FRAMESIZE_VGA`
    -   Gunakan HTTP upload instead of MQTT

### WiFi Disconnects Frequently

-   **Solusi**:
    -   Power supply tidak cukup (gunakan 5V 2A)
    -   Tambah capacitor 100uF pada 5V dan GND
    -   Pastikan WiFi signal kuat (RSSI > -70)

### Images Not Saving

-   **Periksa**:
    -   MQTT subscriber running
    -   Storage disk space
    -   File permissions (`chmod -R 775 storage`)

---

## 9️⃣ **Advanced Features**

### Motion Detection

```cpp
// Compare current frame with previous frame
bool detectMotion(camera_fb_t *fb) {
  static uint32_t lastSum = 0;
  uint32_t currentSum = 0;

  for (int i = 0; i < fb->len; i += 100) {
    currentSum += fb->buf[i];
  }

  bool motion = abs((int)(currentSum - lastSum)) > 100000;
  lastSum = currentSum;

  return motion;
}
```

### Image Compression

```cpp
// Reduce image quality for faster upload
sensor_t * s = esp_camera_sensor_get();
s->set_quality(s, 20);  // Lower quality = smaller file
```

### Scheduled Capture

```cpp
// Capture every hour
if (millis() - lastScheduledCapture > 3600000) {
  captureAndPublishImage();
  lastScheduledCapture = millis();
}
```

---

## 🔟 **Performance Tips**

1. **Optimize Image Size**

    - VGA (640x480) untuk live stream
    - UXGA (1600x1200) untuk capture berkualitas tinggi

2. **Use HTTP for Large Images**

    - MQTT untuk metadata
    - HTTP server di ESP32 untuk download image

3. **Implement Caching**

    - Laravel cache untuk latest image
    - Reduce database queries

4. **Background Processing**
    - Queue untuk image processing
    - Thumbnail generation

---

**Document Version**: 1.0  
**Last Updated**: 2025-12-16  
**Project**: Cat Cage Monitoring System  
**Module**: ESP32 CAM Integration
