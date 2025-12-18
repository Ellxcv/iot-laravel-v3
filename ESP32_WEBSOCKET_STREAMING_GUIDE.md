# ESP32 CAM Firmware Modification Guide

## Overview

Modify `test_cam_esp32_2.ino` untuk send camera frames via MQTT untuk WebSocket streaming.

## Required Changes

### 1. Install Base64 Library di Arduino IDE

**Steps:**

1. Open Arduino IDE
2. Tools → Manage Libraries
3. Search: "base64"
4. Install: **"base64 by Densaugeo"**

### 2. Add Include Statement

**File:** `test_cam_esp32_2.ino`  
**Location:** Line 26 (after `#include <ArduinoJson.h>`)

```cpp
#include <base64.h>  // For base64 encoding frames
```

### 3. Add Stream Topic

**Location:** Line 48 (after existing topic definitions)

```cpp
String TOPIC_STREAM = "iot/devices/" + String(DEVICE_ID) + "/stream";
```

### 4. Increase MQTT Buffer Size

**Location:** Line 476

**Replace:**

```cpp
mqttClient.setBufferSize(4096);  // Old: 4KB
```

**With:**

```cpp
mqttClient.setBufferSize(16384);  // New: 16KB untuk base64 images
```

### 5. Modify Streaming Loop

**Location:** Lines 500-534  
**Replace entire streaming block with:**

```cpp
// Auto-capture if streaming enabled (REVISED for WebSocket)
if (streamingEnabled) {
  static unsigned long lastStreamCapture = 0;
  const unsigned long STREAM_INTERVAL_MS = 250;  // 250ms = ~4 FPS

  if (now - lastStreamCapture >= STREAM_INTERVAL_MS) {
    camera_fb_t *fb = esp_camera_fb_get();
    if (fb) {
      // Encode frame to base64
      String base64Frame = base64::encode(fb->buf, fb->len);

      // Create JSON payload for WebSocket streaming
      StaticJsonDocument<512> streamDoc;
      streamDoc["device_id"] = DEVICE_ID;
      streamDoc["frame"] = base64Frame;  // base64 JPEG
      streamDoc["timestamp"] = millis();
      streamDoc["fps"] = g_fps;
      streamDoc["size"] = fb->len;

      String streamPayload;
      serializeJson(streamDoc, streamPayload);

      // Publish to MQTT stream topic
      if (mqttClient.publish(TOPIC_STREAM.c_str(), streamPayload.c_str())) {
        Serial.printf("[STREAM] Frame sent: %d bytes (base64: %d chars)\\n",
                      fb->len, base64Frame.length());
      } else {
        Serial.println("[STREAM] Publish failed!");
      }

      esp_camera_fb_return(fb);
    }
    lastStreamCapture = now;
  }
}
```

## Summary of Changes

| Change     | Old                         | New                               |
| ---------- | --------------------------- | --------------------------------- |
| **FPS**    | 0.2 FPS (5 second interval) | 4 FPS (250ms interval)            |
| **Format** | HTTP upload JPEG binary     | MQTT base64 JPEG string           |
| **Buffer** | 4KB                         | 16KB                              |
| **Topic**  | N/A                         | `iot/devices/esp32-cam-01/stream` |

## Flash Instructions

1. **Connect ESP32 CAM** to computer via FTDI
2. **Select Board:** Tools → Board → ESP32 Arduino → AI Thinker ESP32-CAM
3. **Select Port:** Tools → Port → (your port)
4. **Upload:** Click Upload button
5. **Open Serial Monitor:** Tools → Serial Monitor (115200 baud)

## Verification

**Expected Serial Output:**

```
[WiFi] Connected: 192.168.1.104
[HTTP] Camera server started on port 80
[MQTT] Connecting to broker... connected!
[MQTT] Client ID: ESP32CAM-a1b2
[MQTT] Subscribed to: iot/devices/esp32-cam-01/commands
```

**After clicking "Start Stream" on website:**

```
[MQTT] Message received on topic: iot/devices/esp32-cam-01/commands
[MQTT] Payload: {"cmd":"stream_start"}
[MQTT] Command: stream_start
[MQTT] Streaming started
[STREAM] Frame sent: 12845 bytes (base64: 17127 chars)
[STREAM] Frame sent: 12834 bytes (base64: 17112 chars)
...
```

## Testing

1. Flash ESP32 with modified code
2. Power on ESP32, connect to WiFi/MQTT
3. Open website: `https://iot-smartcatcage.site/camera/live?device_id=esp32-cam-01`
4. Click **"Start Stream"** button
5. Frames should appear in browser within 1-2 seconds

## Troubleshooting

### Problem: "base64.h: No such file or directory"

**Solution:** Install base64 library via Arduino Library Manager

### Problem: "STREAM Publish failed!"

**Solution:**

-   Check MQTT connection status
-   Verify buffer size increased to 16KB
-   Check HiveMQ Cloud connection

### Problem: Muy high latency (>3 seconds)

**Solution:**

-   Reduce FPS: Change `STREAM_INTERVAL_MS` to 500 (2 FPS)
-   Lower resolution: Change `FRAMESIZE_QVGA` to smaller
-   Check WiFi signal strength

### Problem: ESP32 reboots/crashes

**Solution:**

-   Base64 encoding uses memory, reduce `config.fb_count` to 1
-   Lower JPEG quality (increase `config.jpeg_quality` to 15)
