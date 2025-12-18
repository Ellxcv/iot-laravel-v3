/*
   ESP32-CAM IoT Integration (Hybrid MQTT + HTTP)
   -----------------------------------------------
   Architecture: HYBRID
   - HTTP Server: /stream, /capture, /status, /control (direct access)
   - MQTT Client: Commands, status reporting, metadata publishing
   
   Fitur:
   HTTP Endpoints:
   - /stream         : MJPEG streaming
   - /capture        : Snapshot image
   - /status         : JSON status
   - /control        : Set parameter kamera (flash, framesize, dsb.)
   
   MQTT Topics:
   - iot/devices/esp32-cam-01/status    : Device status (publish)
   - iot/devices/esp32-cam-01/image     : Image metadata (publish)
   - iot/devices/esp32-cam-01/commands  : Remote commands (subscribe)
*/

#include "esp_camera.h"
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <base64.h>  // For base64 encoding frames
#include "esp_timer.h"
#include "img_converters.h"
#include "fb_gfx.h"
#include "esp_http_server.h"
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

// ====================== WIFI ====================== //
const char *WIFI_SSID = "Ell";
const char *WIFI_PASS = "Mizaell14-";

// ====================== MQTT CONFIG (HiveMQ Cloud) ====================== //
const char *MQTT_BROKER = "3ccfdf64ce1f497db3a40c9b37fe1624.s1.eu.hivemq.cloud";
const int MQTT_PORT = 8883;  // TLS/SSL port
const char *MQTT_USERNAME = "mizaell";
const char *MQTT_PASSWORD = "Miegoreng1-";
const char *DEVICE_ID = "esp32-cam-01";

// MQTT Topics
String TOPIC_STATUS = "iot/devices/" + String(DEVICE_ID) + "/status";
String TOPIC_IMAGE = "iot/devices/" + String(DEVICE_ID) + "/image";
String TOPIC_COMMANDS = "iot/devices/" + String(DEVICE_ID) + "/commands";
String TOPIC_STREAM = "iot/devices/" + String(DEVICE_ID) + "/stream";  // WebSocket streaming

// ====================== LARAVEL SERVER (for Image Upload) ====================== //
// Production Server - AWS Lightsail (IP: 47.130.198.138)
const char *LARAVEL_SERVER_URL = "https://iot-smartcatcage.site/api/camera/upload";
const char *LARAVEL_API_KEY = "supersecretkey123";

// =================== FPS MONITOR =================== //
static float g_fps = 0.0f;
static uint64_t g_last_frame_time = 0;

// ================== MQTT TIMER ================== //
unsigned long lastMqttStatusUpdate = 0;
const unsigned long MQTT_STATUS_INTERVAL_MS = 30000;  // publish status tiap 30 dtk

// ================== MQTT CLIENT ================== //
WiFiClientSecure wifiSecureClient;
PubSubClient mqttClient(wifiSecureClient);
bool streamingEnabled = false;

// ====================== PINOUT AI-THINKER ====================== //
#define PWDN_GPIO_NUM 32
#define RESET_GPIO_NUM -1
#define XCLK_GPIO_NUM 0
#define SIOD_GPIO_NUM 26
#define SIOC_GPIO_NUM 27

#define Y9_GPIO_NUM 35
#define Y8_GPIO_NUM 34
#define Y7_GPIO_NUM 39
#define Y6_GPIO_NUM 36
#define Y5_GPIO_NUM 21
#define Y4_GPIO_NUM 19
#define Y3_GPIO_NUM 18
#define Y2_GPIO_NUM 5
#define VSYNC_GPIO_NUM 25
#define HREF_GPIO_NUM 23
#define PCLK_GPIO_NUM 22
#define FLASH_LED_PIN 4

// ============================================================= //
httpd_handle_t camera_httpd = NULL;

// =================== HANDLERS =================== //
static esp_err_t status_handler(httpd_req_t *req) {
  sensor_t *s = esp_camera_sensor_get();
  if (!s) {
    httpd_resp_send_500(req);
    return ESP_FAIL;
  }
  char json[320];
  snprintf(json, sizeof(json),
           "{\"framesize\":%d,\"quality\":%d,\"brightness\":%d,"
           "\"contrast\":%d,\"saturation\":%d,\"hmirror\":%d,\"vflip\":%d,"
           "\"fps\":%.2f}",
           s->status.framesize, s->status.quality, s->status.brightness,
           s->status.contrast, s->status.saturation,
           s->status.hmirror, s->status.vflip,
           g_fps);
  httpd_resp_set_type(req, "application/json");
  return httpd_resp_send(req, json, strlen(json));
}

static esp_err_t control_handler(httpd_req_t *req) {
  char var[32] = { 0 }, val[32] = { 0 }, buf[64];
  if (httpd_req_get_url_query_str(req, buf, sizeof(buf)) != ESP_OK)
    return httpd_resp_send_500(req);

  if (httpd_query_key_value(buf, "var", var, sizeof(var)) != ESP_OK || httpd_query_key_value(buf, "val", val, sizeof(val)) != ESP_OK)
    return httpd_resp_send_500(req);

  sensor_t *s = esp_camera_sensor_get();
  if (!s) return httpd_resp_send_500(req);
  int v = atoi(val);

  if (!strcmp(var, "framesize")) s->set_framesize(s, (framesize_t)v);
  else if (!strcmp(var, "quality")) s->set_quality(s, v);
  else if (!strcmp(var, "brightness")) s->set_brightness(s, v);
  else if (!strcmp(var, "contrast")) s->set_contrast(s, v);
  else if (!strcmp(var, "saturation")) s->set_saturation(s, v);
  else if (!strcmp(var, "hmirror")) s->set_hmirror(s, v);
  else if (!strcmp(var, "vflip")) s->set_vflip(s, v);
  else if (!strcmp(var, "flash")) {
    digitalWrite(FLASH_LED_PIN, v ? HIGH : LOW);
  } else {
    return httpd_resp_send_500(req);
  }
  httpd_resp_set_type(req, "application/json");
  return httpd_resp_send(req, "{\"ok\":true}", HTTPD_RESP_USE_STRLEN);
}

static esp_err_t capture_handler(httpd_req_t *req) {
  camera_fb_t *fb = esp_camera_fb_get();
  if (!fb) return httpd_resp_send_500(req);
  httpd_resp_set_type(req, "image/jpeg");
  httpd_resp_set_hdr(req, "Content-Disposition", "inline; filename=capture.jpg");
  esp_err_t res = httpd_resp_send(req, (const char *)fb->buf, fb->len);
  esp_camera_fb_return(fb);
  return res;
}

#define PART_BOUNDARY "123456789000000000000987654321"
static const char *_STREAM_CONTENT_TYPE = "multipart/x-mixed-replace;boundary=" PART_BOUNDARY;
static const char *_STREAM_BOUNDARY = "\r\n--" PART_BOUNDARY "\r\n";
static const char *_STREAM_PART = "Content-Type: image/jpeg\r\nContent-Length: %u\r\n\r\n";

static esp_err_t stream_handler(httpd_req_t *req) {
  camera_fb_t *fb = NULL;
  httpd_resp_set_type(req, _STREAM_CONTENT_TYPE);

  // init time awal
  g_last_frame_time = esp_timer_get_time();

  while (true) {
    uint64_t now = esp_timer_get_time();
    float dt = (now - g_last_frame_time) / 1000000.0f;  // detik
    if (dt > 0.0f) {
      float inst_fps = 1.0f / dt;
      // smoothing biar nggak lompat-lompat
      if (g_fps <= 0.01f) g_fps = inst_fps;
      else g_fps = g_fps * 0.8f + inst_fps * 0.2f;
    }
    g_last_frame_time = now;

    fb = esp_camera_fb_get();
    if (!fb) break;
    size_t len = fb->len;
    uint8_t *buf = fb->buf;

    char part_buf[64];
    size_t hlen = snprintf(part_buf, 64, _STREAM_PART, len);

    if (httpd_resp_send_chunk(req, _STREAM_BOUNDARY, strlen(_STREAM_BOUNDARY)) != ESP_OK || httpd_resp_send_chunk(req, part_buf, hlen) != ESP_OK || httpd_resp_send_chunk(req, (const char *)buf, len) != ESP_OK) {
      esp_camera_fb_return(fb);
      break;
    }
    esp_camera_fb_return(fb);

    // limit ~10 fps
    vTaskDelay(100 / portTICK_PERIOD_MS);
  }
  return ESP_OK;
}

// =================== CAMERA SERVER =================== //
void startCameraServer() {
  httpd_config_t config = HTTPD_DEFAULT_CONFIG();
  config.server_port = 80;
  if (httpd_start(&camera_httpd, &config) == ESP_OK) {
    httpd_uri_t uri_stream = { .uri = "/stream", .method = HTTP_GET, .handler = stream_handler, .user_ctx = NULL };
    httpd_uri_t uri_status = { .uri = "/status", .method = HTTP_GET, .handler = status_handler, .user_ctx = NULL };
    httpd_uri_t uri_capture = { .uri = "/capture", .method = HTTP_GET, .handler = capture_handler, .user_ctx = NULL };
    httpd_uri_t uri_control = { .uri = "/control", .method = HTTP_GET, .handler = control_handler, .user_ctx = NULL };
    httpd_register_uri_handler(camera_httpd, &uri_stream);
    httpd_register_uri_handler(camera_httpd, &uri_status);
    httpd_register_uri_handler(camera_httpd, &uri_capture);
    httpd_register_uri_handler(camera_httpd, &uri_control);
  }
}

// =================== MQTT CALLBACK HANDLER =================== //
void mqttCallback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (unsigned int i = 0; i < length; i++) {
    message += (char)payload[i];
  }

  Serial.printf("[MQTT] Message received on topic: %s\n", topic);
  Serial.printf("[MQTT] Payload: %s\n", message.c_str());

  // Parse JSON command
  StaticJsonDocument<512> doc;
  DeserializationError error = deserializeJson(doc, message);

  if (error) {
    Serial.println("[MQTT] JSON parse failed");
    return;
  }

  String cmd = doc["cmd"];
  Serial.printf("[MQTT] Command: %s\n", cmd.c_str());

  // Handle commands
  if (cmd == "capture") {
    // Capture single image and upload to Laravel
    camera_fb_t *fb = esp_camera_fb_get();
    if (fb) {
      Serial.printf("[MQTT] Image captured: %d bytes\n", fb->len);
      
      // Upload to Laravel server
      bool uploaded = uploadImageToLaravel(fb);
      
      // Publish metadata via MQTT
      StaticJsonDocument<256> imgDoc;
      imgDoc["device_id"] = DEVICE_ID;
      imgDoc["timestamp"] = millis();
      imgDoc["format"] = "jpeg";
      imgDoc["size"] = fb->len;
      imgDoc["width"] = fb->width;
      imgDoc["height"] = fb->height;
      
      if (uploaded) {
        // Image uploaded to Laravel, users can access via Laravel
        imgDoc["url"] = String(LARAVEL_SERVER_URL).substring(0, String(LARAVEL_SERVER_URL).lastIndexOf('/')) + "/latest/" + String(DEVICE_ID);
        imgDoc["uploaded"] = true;
      } else {
        // Fallback to local HTTP endpoint
        imgDoc["url"] = "http://" + WiFi.localIP().toString() + "/capture";
        imgDoc["uploaded"] = false;
      }
      
      String imgPayload;
      serializeJson(imgDoc, imgPayload);
      mqttClient.publish(TOPIC_IMAGE.c_str(), imgPayload.c_str());
      Serial.println("[MQTT] Image metadata published");
      
      esp_camera_fb_return(fb);
    }
  }
  else if (cmd == "stream_start") {
    streamingEnabled = true;
    Serial.println("[MQTT] Streaming started");
  }
  else if (cmd == "stream_stop") {
    streamingEnabled = false;
    Serial.println("[MQTT] Streaming stopped");
  }
  else if (cmd == "flash_on") {
    digitalWrite(FLASH_LED_PIN, HIGH);
    Serial.println("[MQTT] Flash LED ON");
  }
  else if (cmd == "flash_off") {
    digitalWrite(FLASH_LED_PIN, LOW);
    Serial.println("[MQTT] Flash LED OFF");
  }
  else if (cmd == "set_quality") {
    int quality = doc["params"]["quality"];
    sensor_t *s = esp_camera_sensor_get();
    if (s) {
      s->set_quality(s, quality);
      Serial.printf("[MQTT] Quality set to: %d\n", quality);
    }
  }
  else if (cmd == "set_resolution") {
    String res = doc["params"]["resolution"];
    framesize_t frameSize = FRAMESIZE_VGA;
    
    if (res == "UXGA") frameSize = FRAMESIZE_UXGA;
    else if (res == "SXGA") frameSize = FRAMESIZE_SXGA;
    else if (res == "XGA") frameSize = FRAMESIZE_XGA;
    else if (res == "SVGA") frameSize = FRAMESIZE_SVGA;
    else if (res == "VGA") frameSize = FRAMESIZE_VGA;
    else if (res == "QVGA") frameSize = FRAMESIZE_QVGA;
    
    sensor_t *s = esp_camera_sensor_get();
    if (s) {
      s->set_framesize(s, frameSize);
      Serial.printf("[MQTT] Resolution set to: %s\n", res.c_str());
    }
  }
  else {
    Serial.printf("[MQTT] Unknown command: %s\n", cmd.c_str());
  }
}

// =================== MQTT CONNECTION =================== //
void connectMQTT() {
  while (!mqttClient.connected()) {
    Serial.print("[MQTT] Connecting to broker...");
    
    // Generate random client ID
    String clientId = "ESP32CAM-" + String(random(0xffff), HEX);
    
    if (mqttClient.connect(clientId.c_str(), MQTT_USERNAME, MQTT_PASSWORD)) {
      Serial.println(" connected!");
      Serial.printf("[MQTT] Client ID: %s\n", clientId.c_str());
      
      // Subscribe to commands topic
      mqttClient.subscribe(TOPIC_COMMANDS.c_str());
      Serial.printf("[MQTT] Subscribed to: %s\n", TOPIC_COMMANDS.c_str());
      
      // Publish online status
      publishStatus("online");
      
    } else {
      Serial.printf(" failed, rc=%d\n", mqttClient.state());
      Serial.println("[MQTT] Retrying in 5 seconds...");
      delay(5000);
    }
  }
}

// =================== PUBLISH STATUS =================== //
void publishStatus(String status) {
  StaticJsonDocument<384> doc;
  doc["device_id"] = DEVICE_ID;
  doc["status"] = status;
  doc["uptime"] = millis();
  doc["rssi"] = WiFi.RSSI();
  doc["free_heap"] = ESP.getFreeHeap();
  doc["camera_ready"] = true;
  doc["fps"] = g_fps;
  doc["ip"] = WiFi.localIP().toString();
  
  String payload;
  serializeJson(doc, payload);
  
  mqttClient.publish(TOPIC_STATUS.c_str(), payload.c_str(), true);
  Serial.println("[MQTT] Status published");
}

// =================== UPLOAD IMAGE TO LARAVEL =================== //
bool uploadImageToLaravel(camera_fb_t *fb) {
  if (!fb) {
    Serial.println("[UPLOAD] No frame buffer");
    return false;
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[UPLOAD] WiFi not connected");
    return false;
  }

  WiFiClientSecure client;
  client.setInsecure();  // Skip certificate validation (for ngrok/development)

  HTTPClient http;
  http.begin(client, LARAVEL_SERVER_URL);
  http.setTimeout(15000);  // 15 seconds timeout
  http.addHeader("Content-Type", "image/jpeg");
  http.addHeader("X-Device-ID", DEVICE_ID);
  http.addHeader("X-API-KEY", LARAVEL_API_KEY);

  Serial.printf("[UPLOAD] Uploading %d bytes to Laravel...\n", fb->len);

  int httpCode = http.POST(fb->buf, fb->len);

  if (httpCode > 0) {
    Serial.printf("[UPLOAD] HTTP Response: %d\n", httpCode);
    if (httpCode == 200 || httpCode == 201) {
      String response = http.getString();
      Serial.printf("[UPLOAD] Success! Response: %s\n", response.c_str());
      http.end();
      return true;
    } else {
      String response = http.getString();
      Serial.printf("[UPLOAD] Failed! Response: %s\n", response.c_str());
    }
  } else {
    Serial.printf("[UPLOAD] HTTP Error: %s\n", http.errorToString(httpCode).c_str());
  }

  http.end();
  return false;
}


// =================== CAMERA INIT =================== //
bool init_camera() {
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
  config.frame_size = FRAMESIZE_QVGA;
  config.jpeg_quality = 12;
  config.fb_count = 2;

  if (esp_camera_init(&config) != ESP_OK) return false;

  sensor_t *s = esp_camera_sensor_get();
  s->set_whitebal(s, 1);
  s->set_gain_ctrl(s, 1);
  s->set_exposure_ctrl(s, 1);
  return true;
}

// =================== SETUP =================== //
void setup() {
  // Disable brownout detector untuk stabilitas
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);
  
  pinMode(FLASH_LED_PIN, OUTPUT);
  digitalWrite(FLASH_LED_PIN, LOW);
  Serial.begin(115200);
  Serial.println("\n[BOOT] ESP32-CAM Starting...");
  Serial.println("[BOOT] Architecture: HYBRID (MQTT + HTTP)");

  if (!init_camera()) {
    Serial.println("[ERR] Camera init failed!");
    while (true) delay(1000);
  }
  Serial.println("[CAM] Camera initialized");

  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.printf("[WiFi] Connecting to %s", WIFI_SSID);
  while (WiFi.status() != WL_CONNECTED) {
    delay(300);
    Serial.print(".");
  }
  Serial.printf("\n[WiFi] Connected: %s\n", WiFi.localIP().toString().c_str());

  // Start HTTP Camera Server
  startCameraServer();
  Serial.println("[HTTP] Camera server started on port 80");
  Serial.printf("[HTTP] Stream: http://%s/stream\n", WiFi.localIP().toString().c_str());
  Serial.printf("[HTTP] Capture: http://%s/capture\n", WiFi.localIP().toString().c_str());
  
  // Setup MQTT Client
  wifiSecureClient.setInsecure();  // Skip certificate validation for HiveMQ Cloud
  mqttClient.setServer(MQTT_BROKER, MQTT_PORT);
  mqttClient.setCallback(mqttCallback);
  mqttClient.setBufferSize(16384);  // 16KB buffer for base64 images
  Serial.println("[MQTT] Client configured");
  
  // Connect to MQTT Broker
  connectMQTT();
}

// =================== LOOP =================== //
void loop() {
  unsigned long now = millis();

  // MQTT reconnection logic
  if (!mqttClient.connected()) {
    Serial.println("[MQTT] Connection lost, reconnecting...");
    connectMQTT();
  }
  mqttClient.loop();

  // Publish status heartbeat every 30 seconds
  if (WiFi.status() == WL_CONNECTED && now - lastMqttStatusUpdate >= MQTT_STATUS_INTERVAL_MS) {
    publishStatus("online");
    lastMqttStatusUpdate = now;
  }

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
          Serial.printf("[STREAM] Frame sent: %d bytes (base64: %d chars)\n", 
                        fb->len, base64Frame.length());
        } else {
          Serial.println("[STREAM] Publish failed!");
        }
        
        esp_camera_fb_return(fb);
      }
      lastStreamCapture = now;
    }
  }

  delay(10);
}
