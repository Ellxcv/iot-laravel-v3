# Dokumentasi Struktur Menu - IoT Monitoring System V3

## Overview

Sistem ini memiliki dua level akses dengan struktur menu yang berbeda:

-   **User (Regular User)**: Akses terbatas untuk monitoring dan kontrol perangkat mereka sendiri
-   **Admin**: Akses penuh termasuk manajemen user dan semua perangkat

Menu ditampilkan dalam sidebar yang responsif dengan indicator visual untuk halaman aktif.

---

## 1. Struktur Menu Pengguna (User Role)

### Dashboard & Monitoring

#### 🏠 IoT Status

-   **Route**: `/iot/status` (`iot.status`)
-   **Controller**: `IoTController@status`
-   **Deskripsi**: Halaman utama yang menampilkan status real-time dari semua perangkat IoT
-   **Fitur**:
    -   Dashboard overview dengan card status device
    -   Data sensor terkini (temperature, humidity, air quality, water level, soil moisture, weight)
    -   Grafik real-time sensor readings
    -   Kontrol aktuator (fan, heater, humidifier)
    -   Mode kontrol (Manual/Automatic/Fuzzy)
    -   Status koneksi perangkat (Online/Offline)
-   **Icon**: 🔌 IoT/Chip icon
-   **Access**: ✅ User & Admin

#### 📱 Devices

-   **Route**: `/devices` (`devices.index`)
-   **Controller**: `DeviceController@index`
-   **Deskripsi**: Manajemen perangkat IoT milik user
-   **Fitur**:
    -   Daftar perangkat IoT yang dimiliki user
    -   Status online/offline setiap device
    -   Informasi device (name, type, description, last seen)
    -   Tambah device baru
    -   Edit informasi device
    -   Hapus device
    -   Enable/disable device
-   **Icon**: 🔲 Device/Chip icon
-   **Access**: ✅ User & Admin
-   **Note**: User hanya bisa melihat dan mengelola device mereka sendiri

### Camera

#### 📹 Live Camera

-   **Route**: `/camera/live` (`camera.live`)
-   **Controller**: `CameraController@live`
-   **Deskripsi**: Live streaming dari kamera ESP32-CAM
-   **Fitur**:
    -   Live video stream dari ESP32-CAM
    -   Start/stop streaming
    -   Capture gambar manual
    -   Flash control (on/off)
    -   Quality adjustment
    -   Resolution setting
    -   Gallery akses gambar captured
    -   Device status monitoring (FPS, IP)
-   **Sub-routes**:
    -   `GET /camera/{deviceId}/gallery` - Gallery gambar
    -   `POST /camera/{deviceId}/capture` - Capture foto
    -   `POST /camera/{deviceId}/stream/start` - Mulai streaming
    -   `POST /camera/{deviceId}/stream/stop` - Stop streaming
    -   `POST /camera/{deviceId}/flash` - Kontrol flash
    -   `POST /camera/{deviceId}/quality` - Set quality
    -   `POST /camera/{deviceId}/resolution` - Set resolution
-   **Icon**: 📹 Video camera icon
-   **Access**: ✅ User & Admin

### Notifications

#### 🔔 Notifications

-   **Route**: `/notifications` (`notifications.index`)
-   **Controller**: `NotificationController@index`
-   **Deskripsi**: Konfigurasi dan log notifikasi
-   **Fitur**:
    -   **Telegram Settings**:
        -   Bot token configuration
        -   Chat ID setup
        -   Enable/disable Telegram notifications
        -   Send test notification
    -   **Firebase Cloud Messaging Settings**:
        -   Enable/disable FCM push notifications
        -   Manage device tokens (multi-device support)
        -   Device list (desktop, mobile, tablet)
        -   Remove old/inactive tokens
    -   **Sensor Thresholds** (per device):
        -   Temperature threshold (min/max)
        -   Humidity threshold (min/max)
        -   Air quality threshold (max CO2 PPM)
        -   Enable/disable per sensor type
        -   Cooldown configuration (minutes)
    -   **Notification Logs**:
        -   History of sent notifications
        -   Status (sent/failed)
        -   Timestamp
        -   Error messages
-   **Sub-routes**:
    -   `POST /notifications/settings` - Update settings
    -   `POST /notifications/test` - Send test notification
    -   `GET /notifications/logs` - Get notification logs
    -   `GET /devices/{deviceId}/thresholds` - Get thresholds
    -   `POST /devices/{deviceId}/thresholds` - Update thresholds
-   **Icon**: 🔔 Bell icon
-   **Access**: ✅ User & Admin

### Analytics

#### 📊 Historical Data

-   **Route**: `/history` (`history.index`)
-   **Controller**: `HistoryController@index`
-   **Deskripsi**: Visualisasi data historis sensor dan aktuator
-   **Fitur**:
    -   **Sensor Data History**:
        -   Temperature chart (time-series)
        -   Humidity chart
        -   Air quality (CO2 PPM) chart
        -   Water level chart
        -   Soil moisture chart
        -   Weight chart
    -   **Actuator History**:
        -   Fan duty cycle over time
        -   Heater duty cycle over time
        -   Humidifier duty cycle over time
        -   Control mode history
    -   **Filters**:
        -   Date range selector
        -   Device filter
        -   Sensor type filter
        -   Data type (sensor/actuator)
    -   **Statistics**:
        -   Average values
        -   Min/Max values
        -   Total records
    -   **Export**: Download data as CSV/Excel
-   **Sub-routes**:
    -   `GET /history/data` - Get filtered historical data (AJAX)
-   **Icon**: 📊 Bar chart icon
-   **Access**: ✅ User & Admin

### System

#### 🚪 Logout

-   **Route**: `POST /logout` (`logout`)
-   **Controller**: `AuthController@logout`
-   **Deskripsi**: Keluar dari sistem
-   **Fitur**:
    -   Logout dan clear session
    -   Redirect ke login page
-   **Icon**: 🚪 Logout icon
-   **Access**: ✅ User & Admin

---

## 2. Struktur Menu Admin (Admin Role)

Admin memiliki akses ke **semua menu User** ditambah menu khusus admin di bawah ini:

### Admin Management

#### 👥 Manage Users

-   **Route**: `/admin/users` (`admin.users.index`)
-   **Controller**: `UserController` (Resource Controller)
-   **Deskripsi**: Manajemen pengguna sistem
-   **Fitur**:
    -   **List Users** (`GET /admin/users`):
        -   Daftar semua user
        -   Filter by role (admin/user)
        -   Search by name/email
        -   User statistics
    -   **Create User** (`GET /admin/users/create`):
        -   Form tambah user baru
        -   Set name, email, password, role
    -   **Edit User** (`GET /admin/users/{id}/edit`):
        -   Update user information
        -   Change role (admin/user)
        -   Reset password
    -   **View User** (`GET /admin/users/{id}`):
        -   Detail informasi user
        -   Devices owned by user
        -   Activity history
    -   **Delete User** (`DELETE /admin/users/{id}`):
        -   Hapus user (soft delete recommended)
        -   Transfer devices atau set null
-   **Available Routes**:
    -   `GET /admin/users` - Index (list)
    -   `GET /admin/users/create` - Create form
    -   `POST /admin/users` - Store new user
    -   `GET /admin/users/{id}` - Show detail
    -   `GET /admin/users/{id}/edit` - Edit form
    -   `PUT/PATCH /admin/users/{id}` - Update
    -   `DELETE /admin/users/{id}` - Delete
-   **Icon**: 👥 Users icon
-   **Access**: ⚠️ **Admin Only**
-   **Middleware**: `auth`, `admin`

#### 🔧 Manage Devices

-   **Route**: `/admin/devices` (`admin.devices.index`)
-   **Controller**: `AdminDeviceController` (Resource Controller)
-   **Deskripsi**: Manajemen semua perangkat IoT dalam sistem (admin view)
-   **Fitur**:
    -   **List All Devices** (`GET /admin/devices`):
        -   Daftar semua device dari semua user
        -   Filter by type, status, user
        -   Search by device_id, name
        -   Device statistics
        -   Bulk operations
    -   **Create Device** (`GET /admin/devices/create`):
        -   Form create device baru
        -   Assign ke user tertentu
        -   Set type, name, description, status
    -   **View Device** (`GET /admin/devices/{id}`):
        -   Detail lengkap device
        -   Owner information
        -   Latest sensor data
        -   Actuator states
        -   Command history
        -   Camera images (if camera type)
    -   **Edit Device** (`GET /admin/devices/{id}/edit`):
        -   Update device information
        -   Change owner (user_id)
        -   Enable/disable device
        -   Change type, control mode
    -   **Delete Device** (`DELETE /admin/devices/{id}`):
        -   Hapus device
        -   CASCADE delete sensor_data, actuator_states, etc.
-   **Available Routes**:
    -   `GET /admin/devices` - Index (list all)
    -   `GET /admin/devices/create` - Create form
    -   `POST /admin/devices` - Store new device
    -   `GET /admin/devices/{id}` - Show detail
    -   `GET /admin/devices/{id}/edit` - Edit form
    -   `PUT/PATCH /admin/devices/{id}` - Update
    -   `DELETE /admin/devices/{id}` - Delete
-   **Icon**: 🔧 Settings/Gear icon
-   **Access**: ⚠️ **Admin Only**
-   **Middleware**: `auth`, `admin`
-   **Note**: Berbeda dengan `/devices` (user), admin bisa melihat dan mengelola **semua** device

---

## Perbandingan Akses Menu

| Menu Item           | Route            | User | Admin | Keterangan                         |
| ------------------- | ---------------- | ---- | ----- | ---------------------------------- |
| **IoT Status**      | `/iot/status`    | ✅   | ✅    | Dashboard utama monitoring         |
| **Devices**         | `/devices`       | ✅   | ✅    | User: device sendiri, Admin: semua |
| **Live Camera**     | `/camera/live`   | ✅   | ✅    | Streaming ESP32-CAM                |
| **Notifications**   | `/notifications` | ✅   | ✅    | Setting notifikasi & threshold     |
| **Historical Data** | `/history`       | ✅   | ✅    | Grafik & analisis data             |
| **Manage Users**    | `/admin/users`   | ❌   | ✅    | Admin only - user management       |
| **Manage Devices**  | `/admin/devices` | ❌   | ✅    | Admin only - global device mgmt    |
| **Logout**          | `/logout`        | ✅   | ✅    | Keluar sistem                      |

---

## Middleware & Authorization

### Authentication Middleware (`auth`)

-   Semua route (kecuali login/register) memerlukan autentikasi
-   Redirect ke `/login` jika belum login

### Admin Middleware (`admin`)

-   Route dengan prefix `/admin/*` memerlukan role admin
-   Check: `auth()->user()->isAdmin()`
-   Return 403 Forbidden jika bukan admin

### Implementation

```php
// web.php
Route::middleware('auth')->group(function () {
    // User routes (accessible by all authenticated users)
    Route::get('/iot/status', ...);
    Route::get('/devices', ...);
    Route::get('/camera/live', ...);
    // ... etc

    // Admin-only routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('devices', AdminDeviceController::class);
    });
});
```

---

## Visual Sidebar Menu (UI)

### User Sidebar

```
┌─────────────────────────────┐
│  ⚡ Menu                [X] │
├─────────────────────────────┤
│                             │
│  🔌 IoT Status             │ ← Active state (colored)
│  📱 Devices                 │
│  📹 Live Camera             │
│  🔔 Notifications           │
│  📊 Historical Data         │
│                             │
├─────────────────────────────┤
│  🚪 Logout                  │
└─────────────────────────────┘
```

### Admin Sidebar

```
┌─────────────────────────────┐
│  ⚡ Menu                [X] │
├─────────────────────────────┤
│                             │
│  🔌 IoT Status             │
│  📱 Devices                 │
│  📹 Live Camera             │
│  🔔 Notifications           │
│  📊 Historical Data         │
│                             │
│  👥 Manage Users            │ ← Admin only
│  🔧 Manage Devices          │ ← Admin only
│                             │
├─────────────────────────────┤
│  🚪 Logout                  │
└─────────────────────────────┘
```

### Active State Styling

-   **Active menu**: Background indigo-500/20, border indigo-500/50, text white
-   **Inactive menu**: Text indigo-200, hover bg-white/10
-   **Smooth transitions**: 300ms duration

---

## API Routes (Sub-functionality)

### IoT Control API

```
POST /iot/send-command          - Send generic command
POST /iot/control-feeder        - Control feeder servo
POST /iot/update-actuator       - Update actuator duty cycles
GET  /iot/sensor-data           - Get latest sensor data (AJAX)
GET  /iot/historical-data       - Get time-series data (AJAX)
```

### Device Management API

```
POST   /devices                  - Create new device
PATCH  /devices/{id}/status      - Update device status
DELETE /devices/{id}             - Delete device
```

### Camera Control API

```
POST /camera/{deviceId}/capture        - Capture image
POST /camera/{deviceId}/stream/start   - Start streaming
POST /camera/{deviceId}/stream/stop    - Stop streaming
POST /camera/{deviceId}/flash          - Toggle flash
POST /camera/{deviceId}/quality        - Set quality
POST /camera/{deviceId}/resolution     - Set resolution
GET  /camera/{deviceId}/latest         - Get latest image
GET  /camera/{deviceId}/status         - Get camera status
GET  /camera/{deviceId}/latest-frame   - Get latest frame (stream)
```

### Notification API

```
POST /notifications/settings          - Update notification settings
POST /notifications/test              - Send test notification
GET  /notifications/logs              - Get notification history
GET  /devices/{deviceId}/thresholds   - Get sensor thresholds
POST /devices/{deviceId}/thresholds   - Update thresholds
```

### FCM Token API

```
POST   /api/fcm-tokens          - Register new FCM token
GET    /api/fcm-tokens          - List user's tokens
DELETE /api/fcm-tokens/{id}     - Remove token
POST   /api/fcm-tokens/cleanup  - Cleanup old tokens
```

### History API

```
GET /history/data               - Get filtered historical data (AJAX)
```

---

## Guest Routes (Unauthenticated)

### Authentication

```
GET  /login                     - Show login form
POST /login                     - Process login
GET  /register                  - Show register form
POST /register                  - Process registration
GET  /                          - Redirect to login or dashboard
```

---

## Route Naming Convention

### Pattern

```
{prefix}.{action}
```

### Examples

-   `iot.status` → `/iot/status`
-   `devices.index` → `/devices`
-   `camera.live` → `/camera/live`
-   `admin.users.index` → `/admin/users`
-   `admin.devices.create` → `/admin/devices/create`

### Resource Routes (RESTful)

Menggunakan Laravel Resource Controller convention:

-   `index` - GET /resource
-   `create` - GET /resource/create
-   `store` - POST /resource
-   `show` - GET /resource/{id}
-   `edit` - GET /resource/{id}/edit
-   `update` - PUT/PATCH /resource/{id}
-   `destroy` - DELETE /resource/{id}

---

## Security Features

### CSRF Protection

-   Semua POST/PUT/PATCH/DELETE request memerlukan CSRF token
-   Token di-generate otomatis via `@csrf` directive
-   Middleware: `VerifyCsrfToken`

### Role-Based Access Control (RBAC)

```php
// User model
const ROLE_ADMIN = 'admin';
const ROLE_USER = 'user';

public function isAdmin(): bool {
    return $this->role === self::ROLE_ADMIN;
}
```

### Device Ownership

-   User hanya bisa akses device mereka (`iot_devices.user_id`)
-   Admin bisa akses semua device
-   Validation di controller level

### Rate Limiting

-   API endpoints (camera, commands) harus di-rate limit
-   Recommended: Throttle middleware

---

## Navigation Flow

### User Login → Dashboard

```
1. GET /login
2. POST /login (credentials)
3. Redirect to /dashboard
4. /dashboard redirects to /iot/status
5. User sees IoT Status page
```

### Admin Access Flow

```
1. Login as admin
2. Menu sidebar shows additional items:
   - Manage Users
   - Manage Devices
3. Admin can access /admin/* routes
4. Regular users get 403 Forbidden
```

### Device Management Flow (User)

```
1. Navigate to /devices
2. See only personal devices
3. Can add/edit/delete own devices
4. Can configure thresholds for own devices
```

### Device Management Flow (Admin)

```
1. Navigate to /admin/devices
2. See ALL devices from ALL users
3. Can assign devices to users
4. Can view device details across users
5. Full CRUD operations
```

---

## Recommended Menu Additions (Future)

### User Features

-   **Profile Settings** (`/profile`) - Edit name, email, password
-   **Device Groups** (`/device-groups`) - Group devices logically
-   **Alerts History** (`/alerts`) - Dedicated alert timeline
-   **Dashboard Customization** (`/dashboard/customize`) - Widget layout
-   **Export Reports** (`/reports`) - Generate PDF/Excel reports

### Admin Features

-   **System Settings** (`/admin/settings`) - Global configuration
-   **Activity Logs** (`/admin/logs`) - System audit logs
-   **Database Backup** (`/admin/backup`) - Manual backup trigger
-   **Analytics Dashboard** (`/admin/analytics`) - System-wide analytics
-   **API Tokens** (`/admin/api-tokens`) - API access management

---

## Best Practices

### Menu Organization

1. **Grouping**: Group related items (monitoring, management, settings)
2. **Priority**: Most used features at the top
3. **Visual Hierarchy**: Icons + clear labels
4. **Active State**: Clear indicator of current page
5. **Responsive**: Mobile-friendly sidebar (collapsible)

### Permission Checks

```blade
{{-- In Blade templates --}}
@if(auth()->user()->isAdmin())
    <a href="{{ route('admin.users.index') }}">Manage Users</a>
@endif

@can('update', $device)
    <button>Edit Device</button>
@endcan
```

### Error Handling

-   403 Forbidden untuk unauthorized access
-   404 Not Found untuk missing resources
-   Clear error messages untuk user

---

## File Locations

### Route Definitions

-   **Main routes**: `routes/web.php`
-   **API routes**: `routes/api.php` (if needed)

### Controllers

-   **User controllers**: `app/Http/Controllers/`
-   **Admin controllers**: `app/Http/Controllers/` (prefix: Admin)
-   **Auth controller**: `app/Http/Controllers/AuthController.php`

### Middleware

-   **Auth**: Laravel default (`Authenticate.php`)
-   **Admin**: `app/Http/Middleware/AdminMiddleware.php`

### Views

-   **Layouts**: `resources/views/components/layout.blade.php`
-   **Sidebar**: `resources/views/components/sidebar.blade.php`
-   **User views**: `resources/views/{feature}/`
-   **Admin views**: `resources/views/admin/{feature}/`

### Assets

-   **CSS**: `resources/css/app.css` (Tailwind)
-   **JS**: `resources/js/app.js`

---

## Summary

### User Menu (5 items)

1. IoT Status - Dashboard monitoring
2. Devices - Device management (own devices)
3. Live Camera - ESP32-CAM streaming
4. Notifications - Alert settings & logs
5. Historical Data - Analytics & charts

### Admin Menu (7 items)

1-5. All user menu items (with elevated access) 6. Manage Users - User management 7. Manage Devices - Global device management

### Key Differences

-   **Scope**: User sees own data, Admin sees all
-   **Management**: Admin has user/device CRUD
-   **Authorization**: Middleware-based protection
-   **UI**: Conditional rendering in sidebar

---

**Generated:** 2025-12-20  
**Version:** 3.0.0  
**Author:** IoT Monitoring System Team
