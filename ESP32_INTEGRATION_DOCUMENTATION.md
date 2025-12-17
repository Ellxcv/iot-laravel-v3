# 📋 ESP32 Integration Documentation
**Cat Cage Monitoring System - Product V6**

---

## 1️⃣ **Communication Protocol**

### ✅ Method yang Digunakan:
- **MQTT dengan TLS/SSL**
  - Broker: `3ccfdf64ce1f497db3a40c9b37fe1624.s1.eu.hivemq.cloud`
  - Port: `8883` (TLS/SSL enabled)
  - Username: `mizaell`
  - Password: `Miegoreng1-`
  - TLS/SSL: ✅ **Yes** (ISRG Root X1 - Let's Encrypt)
  - Keep-Alive: `60 seconds`
  - Buffer Size: `1024 bytes`

### 📡 WiFi Configuration:
- SSID: `Ell`
- Password: `Mizaell14-`

---

## 2️⃣ **MQTT Topics**

### Device Information:
- **Device ID**: `esp32-catcage-01` (hardcoded)

### Topics yang ESP32 PUBLISH (kirim data):
```
iot/devices/esp32-catcage-01/telemetry    # Sensor data
iot/devices/esp32-catcage-01/status       # Device status (online/offline)
```

### Topics yang ESP32 SUBSCRIBE (terima command):
```
iot/devices/esp32-catcage-01/commands     # Control commands
```

**Topic Format**: `iot/devices/{device_id}/{type}`

---

## 3️⃣ **Data Format - Telemetry (ESP32 → Server)**

### Complete JSON Example:
```json
{
  "device_id": "esp32-catcage-01",
  "timestamp": "1234567890",
  "sensors": {
    "temperature": 28.50,
    "humidity": 65.20,
    "heat_index": 29.30,
    "mq_adc": 2048.0,
    "mq_vpin": 1.650,
    "mq_vgas": 2.475,
    "mq_baseline": 1.600,
    "odor": 5.2,
    "co2_ppm": 450.5,
    "wl_adc": 1500.0,
    "wl_volt": 1.200,
    "water_level": 75.0,
    "wl_zone": "MID",
    "soil_adc": 2000.0,
    "soil_volt": 1.610,
    "soil_pct": 55.0,
    "soil_zone": "MID",
    "weight_grams": 1250.5
  },
  "status": {
    "fan_duty_pct": 60.0,
    "heater_duty_pct": 30.0,
    "humid_duty_pct": 40.0,
    "humidifier_on": true,
    "heater_on": false,
    "control_mode": "FUZZY"
  }
}
```

### 📊 Sensor Field Details:

| Field | Type | Unit | Range | Description |
|-------|------|------|-------|-------------|
| **temperature** | float | °C | -40 to 80 | DHT22 temperature (calibrated) |
| **humidity** | float | % | 0-100 | DHT22 relative humidity (calibrated) |
| **heat_index** | float | °C | varies | Feels-like temperature |
| **mq_adc** | float | raw | 0-4095 | MQ-135 raw ADC value |
| **mq_vpin** | float | V | 0-3.3 | MQ-135 voltage at ESP32 pin |
| **mq_vgas** | float | V | 0-5.0 | MQ-135 estimated gas voltage |
| **mq_baseline** | float | V | varies | MQ-135 rolling baseline (60 samples) |
| **odor** | float | index | 0-100 | Odor index (EMA smoothed) |
| **co2_ppm** | float | ppm | 10-10000 | CO2 concentration (converted from MQ-135) |
| **wl_adc** | float | raw | 0-4095 | Water level ADC value |
| **wl_volt** | float | V | 0-3.3 | Water level voltage |
| **water_level** | float | % | 0-100 | Water level percentage (EMA smoothed) |
| **wl_zone** | string | - | - | Water zone: DRY, LOW, MID, HIGH |
| **soil_adc** | float | raw | 0-4095 | Soil moisture ADC value |
| **soil_volt** | float | V | 0-3.3 | Soil moisture voltage |
| **soil_pct** | float | % | 0-100 | Soil moisture percentage (EMA smoothed) |
| **soil_zone** | string | - | - | Soil zone: DRY, LOW, MID, HIGH |
| **weight_grams** | float | grams | varies | Load cell weight reading (HX711, EMA smoothed) |

### 🎛️ Actuator Status Fields:

| Field | Type | Unit | Range | Description |
|-------|------|------|-------|-------------|
| **fan_duty_pct** | float | % | 0-100 | Fan PWM duty cycle |
| **heater_duty_pct** | float | % | 0-100 | Heater duty cycle (time-sliced) |
| **humid_duty_pct** | float | % | 0-100 | Humidifier duty cycle (time-sliced) |
| **humidifier_on** | boolean | - | true/false | Humidifier relay actual state |
| **heater_on** | boolean | - | true/false | Heater SSR actual state |
| **control_mode** | string | - | FUZZY/HYST | Control algorithm mode |

---

## 4️⃣ **Sensor Details**

### DHT22 (Temperature & Humidity):
- **Pin**: GPIO 4
- **Field names**:
  - Temperature: `temperature` (float)
  - Humidity: `humidity` (float)
  - Heat index: `heat_index` (float)
- **Data type**: `float`
- **Calibration**:
  - Temperature offset: `-0.50°C`
  - Humidity offset: `-22.30%`
  - Humidity scale: `1.00`
- **Reading interval**: `2000ms` (2 seconds)

### MQ-135 (Gas/Odor/CO2):
- **Pin**: GPIO 34 (ADC)
- **Field names**:
  - Raw ADC: `mq_adc` (float)
  - Pin voltage: `mq_vpin` (float)
  - Gas voltage: `mq_vgas` (float)
  - Baseline: `mq_baseline` (float)
  - Odor index: `odor` (float)
  - CO2 PPM: `co2_ppm` (float)
- **Data type**: `float`
- **Voltage divider**: 10kΩ / 20kΩ (top/bottom)
- **Load resistance (RL)**: `10kΩ`
- **R0 (calibrated)**: `76000Ω` (default, adjustable)
- **PPM Conversion**:
  - Coefficient A: `116.6020682`
  - Coefficient B: `-2.769034857`
  - Formula: `PPM = ((Rs/R0) / A)^(1/B)`
- **Odor Index**:
  - Baseline window: 60 samples
  - EMA smoothing: α = 0.15
  - Range: 0-100
- **Reading interval**: `500ms`

### Water Level Sensor:
- **Pin**: GPIO 35 (ADC)
- **Field names**:
  - Raw ADC: `wl_adc` (float)
  - Voltage: `wl_volt` (float)
  - Percentage: `water_level` (float)
  - Zone: `wl_zone` (string)
- **Data type**: `float` / `string`
- **Format**: Percentage (0-100%)
- **Calibration**:
  - Dry baseline: adjustable (default varies)
  - Wet baseline: adjustable (default varies)
- **Zone classification**: ✅ Yes
  - `DRY`: < 15%
  - `LOW`: 15-35%
  - `MID`: 35-70%
  - `HIGH`: > 70%
- **EMA smoothing**: α = 0.20
- **Reading interval**: `500ms`

### Soil Moisture:
- **Pin**: GPIO 32 (ADC)
- **Field names**:
  - Raw ADC: `soil_adc` (float)
  - Voltage: `soil_volt` (float)
  - Percentage: `soil_pct` (float)
  - Zone: `soil_zone` (string)
- **Data type**: `float` / `string`
- **Format**: Percentage (0-100%)
- **Calibration**:
  - Dry baseline: `3200 ADC`
  - Wet baseline: `1400 ADC`
- **Zone classification**: ✅ Yes
  - `DRY`: < 15%
  - `LOW`: 15-35%
  - `MID`: 35-70%
  - `HIGH`: > 70%
- **EMA smoothing**: α = 0.20
- **Reading interval**: `500ms`

### HX711 (Load Cell - Weight):
- **Pins**:
  - DT (Data): GPIO 21
  - SCK (Clock): GPIO 22
- **Field name**: `weight_grams` (float)
- **Unit**: grams
- **Data type**: `float`
- **Calibrated**: ✅ Yes
- **Calibration factor**: `210.10` (adjustable)
- **Averaging**: 10 samples
- **EMA smoothing**: α = 0.20
- **Deadband**: 2 grams
- **Reading interval**: `200ms`

---

## 5️⃣ **Command Format (Laravel → ESP32)**

### Expected JSON Format:
```json
{
  "id": 123,
  "cmd": "command_name",
  "params": {
    "parameter_name": value
  }
}
```

### Command List:

#### 🔥 Heater Commands:
```json
{"id": 1, "cmd": "heat on"}        // or "heater_on"
{"id": 2, "cmd": "heat off"}       // or "heater_off"
{"id": 3, "cmd": "heat auto"}      // or "heater_auto"
```

#### 💧 Humidifier Commands:
```json
{"id": 4, "cmd": "hum on"}         // or "humid_on"
{"id": 5, "cmd": "hum off"}        // or "humid_off"
{"id": 6, "cmd": "hum auto"}       // or "humid_auto"
```

#### 🌪️ Fan Command:
```json
{
  "id": 7,
  "cmd": "fan",
  "params": {
    "percent": 60
  }
}
```
- `percent`: 0-100 (integer or float)
- Minimum run percentage: 20% (below this, fan won't spin due to torque)

#### 🔧 Servo Command:
```json
{
  "id": 8,
  "cmd": "servo",
  "params": {
    "angle": 90
  }
}
```
- Alternative: `"cmd": "sv"`
- `angle`: 0-180 degrees
- Pulse width: 600-2400 μs

#### 🍽️ Feeder Commands:

**Open Feeder Door:**
```json
{"id": 9, "cmd": "feed open"}      // or "feeder_open"
```

**Close Feeder Door:**
```json
{"id": 10, "cmd": "feed close"}    // or "feeder_close"
```

**Start Feeding Cycle:**
```json
{
  "id": 11,
  "cmd": "feed start",
  "params": {
    "times": 3
  }
}
```
- Alternative: `"cmd": "feeder_start"`
- `times`: 1-10 (number of feed cycles)
- Default: 1 if not specified
- **Feeder Configuration**:
  - Open angle: `150°`
  - Close angle: `90°`
  - Hold time: `1200ms`
  - Cooldown: `1000ms`

**Abort Feeding:**
```json
{"id": 12, "cmd": "feed abort"}    // or "feeder_abort"
```

**Preset Feeder Configuration:**
```json
{"id": 13, "cmd": "feed preset"}   // or "feeder_preset"
```
- Resets to default angles and timings

---

## 6️⃣ **Status Messages (ESP32 → Server)**

### Online Status:
```json
{
  "device_id": "esp32-catcage-01",
  "status": "online",
  "uptime": 12345,
  "rssi": -65,
  "free_heap": 123456
}
```

### Offline Status (Last Will Testament):
```json
{
  "device_id": "esp32-catcage-01",
  "status": "offline"
}
```

**Published to**: `iot/devices/esp32-catcage-01/status`
- QoS: `1` (at least once)
- Retained: `true`

---

## 7️⃣ **Device Identification**

- **Method**: ✅ Hardcoded device ID
- **Device ID**: `esp32-catcage-01`
- **Value**: Fixed in code (defined in `MqttClient.cpp`)

---

## 8️⃣ **Timing & Frequency**

| Parameter | Value |
|-----------|-------|
| **Telemetry publish interval** | `5000ms` (5 seconds) |
| **Sending mode** | ✅ Continuous |
| **MQTT reconnect interval** | `5000ms` (5 seconds) |
| **WiFi reconnect interval** | `5000ms` (5 seconds) |
| **MQTT Keep-Alive** | `60 seconds` |

### Sensor Reading Intervals:
| Sensor | Interval |
|--------|----------|
| DHT22 | 2000ms |
| MQ-135 | 500ms |
| Water Level | 500ms |
| Soil Moisture | 500ms |
| HX711 (Weight) | 200ms |

### Control Loop Intervals:
| Task | Interval |
|------|----------|
| Fuzzy evaluation | 250ms |
| Humidifier control | 500ms |
| Heater control | 500ms |

---

## 9️⃣ **Authentication & Security**

### MQTT Authentication:
- **Method**: ✅ Username/Password
- **Username**: `mizaell`
- **Password**: `Miegoreng1-`

### Encryption:
- **TLS/SSL**: ✅ Enabled
- **Certificate**: ISRG Root X1 (Let's Encrypt)
- **Port**: `8883` (TLS)

### Certificate Details:
```
Certificate Authority: Internet Security Research Group (ISRG)
Root: ISRG Root X1
Valid: 2015-06-04 to 2035-06-04
```

---

## 🔟 **Special Features**

### MQ-135 PPM Conversion:
- ✅ CO2 PPM calculation using Rs/R0 ratio
- ✅ R0 calibration support
- ✅ Formula: `PPM = ((Rs/R0) / A)^(1/B)`

### Sensor Calibration:
- ✅ DHT22 offset correction (temperature & humidity)
- ✅ Water level dry/wet baseline calibration
- ✅ Soil moisture dry/wet baseline calibration
- ✅ HX711 load cell calibration factor
- ✅ MQ-135 R0 calibration

### Data Smoothing:
- ✅ EMA (Exponential Moving Average) filtering on:
  - MQ-135 odor index (α = 0.15)
  - Water level (α = 0.20)
  - Soil moisture (α = 0.20)
  - Weight (α = 0.20)

### Time-Slice Control:
- ✅ Heater: 4-second window (SSR-40DA AC zero-cross)
- ✅ Humidifier: 60-second window (relay longevity)
- ✅ Slew rate limiting for smooth transitions

### Last Will Testament (LWT):
- ✅ Automatic "offline" status when ESP32 disconnects
- ✅ Retained message on status topic

---

## 1️⃣1️⃣ **Code Snippets**

### Publish Telemetry Function:
```cpp
void MqttClient_publishTelemetry(const TelemetryData &d) {
  if (!mqttClient.connected()) {
    Serial.println("[MQTT] Cannot publish telemetry - not connected");
    return;
  }
  
  // Build JSON payload
  String payload = "{";
  payload += "\"device_id\":\"" + String(DEVICE_ID) + "\",";
  payload += "\"timestamp\":\"" + String(millis()) + "\",";
  
  // Sensors
  payload += "\"sensors\":{";
  payload += "\"temperature\":" + String(d.tC, 2) + ",";
  payload += "\"humidity\":" + String(d.hPct, 2) + ",";
  // ... (full payload construction)
  payload += "}";
  
  // Publish with QoS 0
  mqttClient.publish(TOPIC_TELEMETRY.c_str(), payload.c_str(), false);
}
```

### Command Handler Function:
```cpp
static void mqttCallback(char* topic, byte* payload, unsigned int length) {
  // Convert payload to string
  String message = "";
  for (unsigned int i = 0; i < length; i++) {
    message += (char)payload[i];
  }
  
  // Parse JSON
  String cmdId = extractJsonValue(message, "id");
  String cmd = extractJsonValue(message, "cmd");
  String params = extractJsonObject(message, "params");
  
  // Execute command
  applyMqttCommand(cmd, params);
}
```

---

## 1️⃣2️⃣ **Testing Scenarios**

### ✅ Normal Operation:
- Sensor data published every 5 seconds
- All sensors reporting valid values
- Actuators responding to fuzzy control

### ✅ Command Execution:
- Feeder commands (open/close/start/abort)
- Actuator control (heater/humidifier/fan)
- Servo positioning

### ✅ Reconnection Handling:
- WiFi reconnection after disconnect
- MQTT reconnection with LWT
- Automatic resubscription to command topic

### ✅ Error Handling:
- Invalid command format detection
- Missing parameter handling
- Connection failure recovery

---

## 1️⃣3️⃣ **Hardware Configuration**

### Pin Assignments:
| Component | Pin | Type |
|-----------|-----|------|
| DHT22 | GPIO 4 | Digital I/O |
| MQ-135 | GPIO 34 | ADC |
| Water Level | GPIO 35 | ADC |
| Soil Moisture | GPIO 32 | ADC |
| HX711 DT | GPIO 21 | Digital I/O |
| HX711 SCK | GPIO 22 | Digital I/O |
| Servo | GPIO 23 | PWM |
| Fan PWM | GPIO 25 | PWM |
| Humidifier Relay | GPIO 14 | Digital Output |
| Heater SSR | GPIO 33 | Digital Output |

### Hardware Notes:
- **Relay**: Active LOW (triggers on LOW signal)
- **Heater SSR**: Active LOW (sink current mode)
- **Voltage Reference**: 3.3V (ESP32 ADC)
- **ADC Resolution**: 12-bit (0-4095)

---

## 📝 **Quick Reference**

### MQTT Connection:
```
Protocol: MQTT over TLS
Broker: 3ccfdf64ce1f497db3a40c9b37fe1624.s1.eu.hivemq.cloud:8883
Username: mizaell
Password: Miegoreng1-
Device ID: esp32-catcage-01
```

### Topic Structure:
```
Commands:   iot/devices/esp32-catcage-01/commands
Telemetry:  iot/devices/esp32-catcage-01/telemetry
Status:     iot/devices/esp32-catcage-01/status
```

### Key Files:
- `Config.h` - Hardware configuration & calibration
- `MqttClient.cpp` - MQTT communication logic
- `SensorManager.cpp` - Sensor reading & processing
- `SystemState.h` - Data structures
- `Actuators.cpp` - Actuator control
- `FuzzyControl.cpp` - Fuzzy logic controller

---

**Document Version**: 1.0  
**Last Updated**: 2025-12-14  
**Project**: Cat Cage Monitoring System (Product V6)  
**Author**: Auto-generated from ESP32 source code
