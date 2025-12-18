# Production Deployment Guide - AWS Lightsail WebSocket Streaming

## 🎯 Objective

Deploy WebSocket camera streaming ke AWS Lightsail production server.

## ✅ Pre-requisites

-   [x] Local testing sudah berhasil
-   [x] Pusher account created
-   [x] ESP32 firmware sudah di-flash dengan WebSocket code
-   [ ] SSH access ke AWS Lightsail server

---

## 📋 Deployment Steps

### **Step 1: Push Code ke Git Repository**

Commit dan push semua changes ke Git:

```bash
cd c:\Users\ACER\Documents\Sem7\Code\Web\V3

# Check status
git status

# Add all changes
git add .

# Commit
git commit -m "Added WebSocket camera streaming with Pusher"

# Push to main branch
git push origin main
```

---

### **Step 2: SSH ke AWS Server**

```bash
ssh user@iot-smartcatcage.site
# atau
ssh user@47.130.198.138
```

**Replace `user` dengan username AWS Anda** (biasanya `ubuntu` atau `admin`)

---

### **Step 3: Pull Latest Code**

```bash
cd /var/www/iot-laravel-v3
# atau path Laravel project Anda di server

# Pull latest changes
git pull origin main
```

---

### **Step 4: Install Pusher Package**

```bash
composer require pusher/pusher-php-server
```

**Expected output:**

```
Installing pusher/pusher-php-server (7.2.4)
Package manifest generated successfully.
```

---

### **Step 5: Update .env with Pusher Credentials**

```bash
nano .env
# atau
vi .env
```

**Add/Update these lines:**

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=ap1
```

**Save:** `Ctrl+X` → `Y` → `Enter`

---

### **Step 6: Clear Laravel Cache**

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### **Step 7: Setup MQTT Subscriber as Systemd Service**

Create service file:

```bash
sudo nano /etc/systemd/system/laravel-mqtt-camera.service
```

**Paste this content:**

```ini
[Unit]
Description=Laravel MQTT Camera Subscriber
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/iot-laravel-v3
ExecStart=/usr/bin/php /var/www/iot-laravel-v3/artisan mqtt:camera-subscribe
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

**Important:** Ganti `/var/www/iot-laravel-v3` dengan path Laravel Anda yang sebenarnya!

**Save:** `Ctrl+X` → `Y` → `Enter`

---

### **Step 8: Enable and Start Service**

```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable service (auto-start on boot)
sudo systemctl enable laravel-mqtt-camera

# Start service
sudo systemctl start laravel-mqtt-camera

# Check status
sudo systemctl status laravel-mqtt-camera
```

**Expected output:**

```
● laravel-mqtt-camera.service - Laravel MQTT Camera Subscriber
   Loaded: loaded (/etc/systemd/system/laravel-mqtt-camera.service; enabled)
   Active: active (running) since Wed 2025-12-18 07:30:00 UTC; 5s ago
```

**✅ Green "active (running)" = Success!**

---

### **Step 9: Check Service Logs**

```bash
# Real-time logs
sudo journalctl -u laravel-mqtt-camera -f

# Last 50 lines
sudo journalctl -u laravel-mqtt-camera -n 50
```

**Expected logs:**

```
Starting ESP32 CAM MQTT subscriber...
Subscribed to camera topics. Listening...
```

---

### **Step 10: Test from Browser**

Open browser:

```
https://iot-smartcatcage.site/camera/live?device_id=esp32-cam-01
```

**Expected:**

1. ✅ WebSocket connects to Pusher
2. ✅ Status shows "Connecting..." then "Live" (green)
3. ✅ Click "Start Stream" button
4. ✅ ESP32 starts sending frames
5. ✅ Browser displays real-time stream @ 3-4 FPS

---

## 🔍 Verification Checklist

-   [ ] Git push successful
-   [ ] SSH to AWS server successful
-   [ ] `git pull` updated code
-   [ ] Pusher package installed
-   [ ] `.env` configured with Pusher credentials
-   [ ] Laravel cache cleared
-   [ ] MQTT service created
-   [ ] MQTT service enabled and running
-   [ ] Service logs show "Subscribed to camera topics"
-   [ ] Browser can access `https://iot-smartcatcage.site/camera/live`
-   [ ] WebSocket connects (check browser console)
-   [ ] Stream works when "Start Stream" clicked
-   [ ] FPS shows 3-4 FPS

---

## 🐛 Troubleshooting

### **Problem: "composer: command not found"**

**Solution:**

```bash
# Check composer location
which composer

# If not found, use full path
/usr/local/bin/composer require pusher/pusher-php-server
```

### **Problem: Service fails to start**

**Check:**

```bash
# View error logs
sudo journalctl -u laravel-mqtt-camera -n 100

# Common issues:
# 1. Wrong WorkingDirectory path
# 2. Wrong User (should be www-data or ubuntu)
# 3. PHP not in PATH - use full path: /usr/bin/php
```

### **Problem: WebSocket tidak connect**

**Check:**

1. Browser console errors
2. Pusher credentials benar di `.env`
3. `php artisan config:clear` sudah dijalankan
4. Check Pusher dashboard (Debug Console) untuk events

### **Problem: MQTT service running tapi tidak terima frames**

**Check:**

```bash
# Stop local mqtt subscriber di laptop
# Cek ESP32 publish ke broker
mosquitto_sub -h 3ccfdf64ce1f497db3a40c9b37fe1624.s1.eu.hivemq.cloud -p 8883 \
  -u mizaell -P "Miegoreng1-" \
  --cafile /etc/ssl/certs/ca-certificates.crt \
  -t "iot/devices/+/stream" -v
```

---

## 🔄 Service Management Commands

```bash
# Start service
sudo systemctl start laravel-mqtt-camera

# Stop service
sudo systemctl stop laravel-mqtt-camera

# Restart service
sudo systemctl restart laravel-mqtt-camera

# Check status
sudo systemctl status laravel-mqtt-camera

# View logs
sudo journalctl -u laravel-mqtt-camera -f

# Disable auto-start
sudo systemctl disable laravel-mqtt-camera
```

---

## 📊 Performance Monitoring

### **Check Pusher Usage**

Go to: https://dashboard.pusher.com/apps/YOUR_APP_ID

**Monitor:**

-   Concurrent connections
-   Messages per day
-   Ensure within free tier limits (100 connections, 200k messages/day)

### **Check Server Resources**

```bash
# CPU and Memory
htop

# MQTT process
ps aux | grep mqtt:camera-subscribe
```

---

## ✅ Deployment Complete!

Setelah semua steps selesai, Anda akan punya:

-   ✅ WebSocket streaming running di production
-   ✅ MQTT subscriber auto-start on boot
-   ✅ Real-time camera monitoring accessible via HTTPS
-   ✅ ESP32 langsung streaming ke production server

**URL Production:** `https://iot-smartcatcage.site/camera/live?device_id=esp32-cam-01`

🎉 **Congratulations!** Real-time WebSocket camera streaming is now LIVE! 🚀
