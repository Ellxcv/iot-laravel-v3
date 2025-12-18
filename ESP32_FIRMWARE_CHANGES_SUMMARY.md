# ESP32 CAM Firmware Modifications - Summary

## ✅ Changes Applied to `test_cam_esp32_2.ino`

### 1. **Added Base64 Library**

**Line 27:**

```cpp
#include <base64.h>  // For base64 encoding frames
```

### 2. **Added Stream Topic**

**Line 50:**

```cpp
String TOPIC_STREAM = "iot/devices/" + String(DEVICE_ID) + "/stream";  // WebSocket streaming
```

### 3. **Increased MQTT Buffer Size**

**Line 478:**

```cpp
mqttClient.setBufferSize(16384);  // 16KB buffer for base64 images (was 4096)
```

### 4. **Replaced Streaming Loop (Lines 502-536)**

**Changed from:**

-   HTTP upload every 5 seconds (0.2 FPS)
-   Binary JPEG to Laravel server

**Changed to:**

-   MQTT publish every 250ms (4 FPS)
-   Base64 encoded JPEG via MQTT
-   Published to `iot/devices/esp32-cam-01/stream`

**Key features:**

-   `STREAM_INTERVAL_MS = 250` (4 FPS)
-   Base64 encoding: `base64::encode(fb->buf, fb->len)`
-   JSON payload with: `device_id`, `frame`, `timestamp`, `fps`, `size`
-   Serial logging: `[STREAM] Frame sent: X bytes (base64: Y chars)`

---

## 📋 Next Steps

### **Before Flashing:**

1. **Install Base64 Library in Arduino IDE**
    - Tools → Manage Libraries
    - Search: "base64"
    - Install: **"base64 by Densaugeo"**

### **Flash ESP32:**

1. **Connect ESP32 CAM** to computer via FTDI
2. **Select Board:** Tools → Board → ESP32 Arduino → AI Thinker ESP32-CAM
3. **Select Port:** Tools → Port → (your COM port)
4. **Upload:** Click Upload button (⬆️)
5. **Wait** for "Connecting...." then "Writing at 0x..."
6. **Complete** when "Hash of data verified"

### **Verify Serial Monitor:**

Open Serial Monitor (Tools → Serial Monitor, 115200 baud):

**Expected output:**

```
[BOOT] ESP32-CAM Starting...
[BOOT] Architecture: HYBRID (MQTT + HTTP)
[CAM] Camera initialized
[WiFi] Connecting to Ell...
[WiFi] Connected: 192.168.1.104
[HTTP] Camera server started on port 80
[MQTT] Client configured
[MQTT] Connecting to broker... connected!
[MQTT] Client ID: ESP32CAM-a1b2
[MQTT] Subscribed to: iot/devices/esp32-cam-01/commands
[MQTT] Status published
```

---

## 🧪 Testing Instructions

### **1. Start Laravel MQTT Subscriber**

Open terminal in Laravel project:

```bash
cd c:\Users\ACER\Documents\Sem7\Code\Web\V3
php artisan mqtt:camera-subscribe
```

**Expected:**

```
Starting ESP32 CAM MQTT subscriber...
Subscribed to camera topics. Listening...
```

### **2. Start Streaming from Web**

1. Open browser: `http://localhost:8000/camera/live?device_id=esp32-cam-01`
2. Click **"Start Stream"** button

**ESP32 Serial Monitor should show:**

```
[MQTT] Message received on topic: iot/devices/esp32-cam-01/commands
[MQTT] Payload: {"id":1234,"cmd":"stream_start"}
[MQTT] Command: stream_start
[MQTT] Streaming started
[STREAM] Frame sent: 12845 bytes (base64: 17127 chars)
[STREAM] Frame sent: 12834 bytes (base64: 17112 chars)
[STREAM] Frame sent: 12801 bytes (base64: 17068 chars)
...
```

**Laravel MQTT Subscriber should show:**

```
[STREAM] esp32-cam-01: 17127 chars (base64) | FPS: 4.2
[STREAM] esp32-cam-01: 17112 chars (base64) | FPS: 4.1
...
```

**Browser should show:**

-   Status: **Green "Live"** indicator
-   FPS: **~3-4 FPS**
-   Real-time camera stream
-   Console log: `[WebSocket] Frame received: ...`

---

## 🐛 Troubleshooting

### **Problem: "base64.h: No such file or directory"**

**Solution:** Install base64 library first (see "Before Flashing" above)

### **Problem: ESP32 reboots/crashes during streaming**

**Solutions:**

1. Lower FPS: Change `STREAM_INTERVAL_MS` to `500` (2 FPS)
2. Reduce frame buffer: Change line 431 `config.fb_count = 2` to `1`
3. Lower resolution: Change line 429 `FRAMESIZE_QVGA` to `FRAMESIZE_QQVGA`

### **Problem: "[STREAM] Publish failed!"**

**Check:**

-   MQTT connection status (should show "connected!")
-   Buffer size is 16384 (not 4096)
-   HiveMQ Cloud credentials correct

### **Problem: High memory usage**

**Solution:** Base64 encoding uses ~33% more memory. Lower resolution or FPS if ESP32 crashes.

---

## 📊 Summary

| Item               | Value                             |
| ------------------ | --------------------------------- |
| **Files Modified** | 1 (`test_cam_esp32_2.ino`)        |
| **Lines Added**    | ~10 lines                         |
| **Lines Modified** | ~35 lines                         |
| **FPS**            | 4 FPS (was 0.2 FPS)               |
| **Protocol**       | MQTT base64 (was HTTP binary)     |
| **Topic**          | `iot/devices/esp32-cam-01/stream` |
| **Buffer Size**    | 16KB (was 4KB)                    |

---

## ✨ Ready to Flash!

Firmware sudah siap! Tinggal:

1. Install base64 library
2. Flash ke ESP32
3. Test streaming

Good luck! 🚀
