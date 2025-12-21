# Application Sitemap & User Journey - IoT Monitoring System V3

## Complete Application Sitemap

```mermaid
graph TD
    Root["/"] --> CheckAuth{Authenticated?}
    CheckAuth -->|No| Login[Login Page]
    CheckAuth -->|Yes| Dashboard[IoT Status]

    Login --> LoginForm[POST /login]
    LoginForm --> Dashboard

    Login --> RegisterLink[Register Link]
    RegisterLink --> Register[Register Page]
    Register --> RegisterForm[POST /register]
    RegisterForm --> Dashboard

    Dashboard --> IoTStatus[IoT Status]
    Dashboard --> Devices[Devices]
    Dashboard --> Camera[Live Camera]
    Dashboard --> Notifications[Notifications]
    Dashboard --> History[Historical Data]
    Dashboard --> CheckAdmin{Is Admin?}

    CheckAdmin -->|Yes| AdminUsers[Manage Users]
    CheckAdmin -->|Yes| AdminDevices[Manage Devices]

    IoTStatus --> SensorData[Real-time Sensor Data]
    IoTStatus --> ActuatorControl[Actuator Control]
    IoTStatus --> DeviceStatus[Device Status]

    Devices --> DeviceList[Device List]
    DeviceList --> AddDevice[Add Device]
    DeviceList --> EditDevice[Edit Device]
    DeviceList --> DeleteDevice[Delete Device]
    DeviceList --> DeviceThresholds[Configure Thresholds]

    Camera --> LiveStream[Live Video Stream]
    Camera --> CameraGallery[Image Gallery]
    LiveStream --> CaptureImage[Capture Image]
    LiveStream --> StreamControl[Start/Stop Stream]
    LiveStream --> FlashControl[Flash Control]
    LiveStream --> QualitySettings[Quality Settings]

    Notifications --> TelegramSettings[Telegram Config]
    Notifications --> FCMSettings[Firebase FCM Config]
    Notifications --> ThresholdConfig[Sensor Thresholds]
    Notifications --> NotifLogs[Notification Logs]
    Notifications --> TestNotif[Send Test]

    History --> SensorHistory[Sensor Data Charts]
    History --> ActuatorHistory[Actuator History Charts]
    History --> FilterData[Date/Device Filter]
    History --> ExportData[Export Data]

    AdminUsers --> UserList[List All Users]
    UserList --> CreateUser[Create User]
    UserList --> EditUser[Edit User]
    UserList --> ViewUser[View User Details]
    UserList --> DeleteUser[Delete User]

    AdminDevices --> AllDeviceList[List All Devices]
    AllDeviceList --> CreateAdminDevice[Create Device]
    AllDeviceList --> EditAdminDevice[Edit Device]
    AllDeviceList --> ViewAdminDevice[View Device Details]
    AllDeviceList --> DeleteAdminDevice[Delete Device]
    AllDeviceList --> AssignDevice[Assign to User]

    Dashboard --> Logout[Logout]
    Logout --> Login

    style Dashboard fill:#4f46e5,stroke:#312e81,stroke-width:3px,color:#fff
    style AdminUsers fill:#dc2626,stroke:#991b1b,stroke-width:2px,color:#fff
    style AdminDevices fill:#dc2626,stroke:#991b1b,stroke-width:2px,color:#fff
    style Login fill:#059669,stroke:#065f46,stroke-width:2px,color:#fff
```

## User Journey Flows

### 1. Regular User Journey

```mermaid
journey
    title Regular User Daily Workflow
    section Authentication
      Open App: 5: User
      Login: 4: User
      Redirect to Dashboard: 5: System
    section Monitoring
      View IoT Status: 5: User
      Check Sensor Data: 5: User
      View Device Status: 4: User
    section Control
      Adjust Actuator: 4: User
      Switch Control Mode: 4: User
      Monitor Changes: 5: User
    section Camera
      Open Live Camera: 4: User
      View Stream: 5: User
      Capture Image: 3: User
    section Alerts
      Configure Thresholds: 3: User
      Setup Telegram: 3: User
      Receive Alerts: 5: System
    section Analytics
      View Historical Data: 4: User
      Filter by Date: 4: User
      Export Report: 3: User
```

### 2. Admin Journey

```mermaid
journey
    title Admin Management Workflow
    section User Management
      Access Admin Panel: 5: Admin
      View All Users: 5: Admin
      Create New User: 4: Admin
      Assign Devices: 4: Admin
    section Device Management
      View All Devices: 5: Admin
      Monitor System Health: 5: Admin
      Create Device: 4: Admin
      Assign to User: 4: Admin
    section System Monitoring
      Check All Sensor Data: 5: Admin
      Review Alerts: 4: Admin
      Verify Device Status: 5: Admin
```

### 3. First-Time User Onboarding Journey

```mermaid
sequenceDiagram
    participant U as New User
    participant S as System
    participant D as Device
    participant N as Notification

    U->>S: Register Account
    S->>U: Welcome Email
    U->>S: Login
    S->>U: Redirect to Dashboard

    Note over U,S: Dashboard shows empty state

    U->>S: Navigate to Devices
    U->>S: Click "Add Device"
    S->>U: Show Device Form
    U->>S: Submit Device Info
    S->>D: Register Device
    D->>S: Confirm Registration
    S->>U: Device Added Successfully

    U->>S: Navigate to Notifications
    U->>S: Configure Telegram
    S->>N: Save Settings
    U->>S: Send Test Notification
    N->>U: Receive Test on Telegram

    U->>S: Configure Thresholds
    S->>U: Thresholds Saved

    Note over U,D: Device starts sending data

    D->>S: Send Sensor Data
    S->>U: Update Dashboard
    U->>S: View Real-time Data

    alt Threshold Exceeded
        S->>N: Trigger Alert
        N->>U: Send Notification
        U->>S: View Alert in App
    end
```

## Page Hierarchy & Navigation

### Complete Page Tree

```
Application Root (/)
│
├── 🔓 Guest Pages
│   ├── Login (/login)
│   │   └── POST Login Form
│   └── Register (/register)
│       └── POST Register Form
│
├── 🔐 Authenticated Pages
│   │
│   ├── 📊 Main Dashboard
│   │   └── IoT Status (/iot/status) [DEFAULT]
│   │       ├── Sensor Data Cards
│   │       ├── Real-time Charts
│   │       └── Actuator Controls
│   │
│   ├── 📱 Devices (/devices)
│   │   ├── Device List View
│   │   ├── Add Device Modal
│   │   ├── Edit Device Modal
│   │   └── Threshold Configuration
│   │       └── (/devices/{id}/thresholds)
│   │
│   ├── 📹 Camera (/camera)
│   │   ├── Live Stream (/camera/live)
│   │   │   ├── Video Feed
│   │   │   ├── Camera Controls
│   │   │   └── Capture Button
│   │   └── Gallery (/camera/{deviceId}/gallery)
│   │       └── Image Grid View
│   │
│   ├── 🔔 Notifications (/notifications)
│   │   ├── Settings Tab
│   │   │   ├── Telegram Config
│   │   │   ├── FCM Config
│   │   │   └── Test Notification
│   │   ├── Thresholds Tab
│   │   │   └── Per-device Sensor Thresholds
│   │   └── Logs Tab
│   │       └── Notification History
│   │
│   ├── 📊 Historical Data (/history)
│   │   ├── Sensor Charts Tab
│   │   │   ├── Temperature Chart
│   │   │   ├── Humidity Chart
│   │   │   ├── Air Quality Chart
│   │   │   ├── Water Level Chart
│   │   │   ├── Soil Moisture Chart
│   │   │   └── Weight Chart
│   │   ├── Actuator Charts Tab
│   │   │   ├── Fan Duty Chart
│   │   │   ├── Heater Duty Chart
│   │   │   └── Humidifier Duty Chart
│   │   └── Filters Panel
│   │       ├── Date Range
│   │       ├── Device Selector
│   │       └── Export Button
│   │
│   └── 🔐 Admin Pages (/admin)
│       │
│       ├── 👥 Manage Users (/admin/users)
│       │   ├── User List (/admin/users)
│       │   ├── Create User (/admin/users/create)
│       │   ├── Edit User (/admin/users/{id}/edit)
│       │   ├── View User (/admin/users/{id})
│       │   └── Delete User (DELETE /admin/users/{id})
│       │
│       └── 🔧 Manage Devices (/admin/devices)
│           ├── Device List (/admin/devices)
│           ├── Create Device (/admin/devices/create)
│           ├── Edit Device (/admin/devices/{id}/edit)
│           ├── View Device (/admin/devices/{id})
│           └── Delete Device (DELETE /admin/devices/{id})
```

## User Flow Diagrams

### IoT Monitoring Flow

```mermaid
flowchart LR
    A[User Logs In] --> B[Dashboard Loaded]
    B --> C{Device Online?}
    C -->|Yes| D[Display Sensor Data]
    C -->|No| E[Show Offline Status]

    D --> F[Real-time Updates]
    F --> G{Threshold Exceeded?}

    G -->|Yes| H[Trigger Alert]
    H --> I[Send Telegram/FCM]
    I --> J[Log Notification]

    G -->|No| K[Continue Monitoring]
    K --> F

    E --> L[Check Last Seen]
    L --> M[Display Warning]
```

### Device Control Flow

```mermaid
flowchart TD
    A[User Opens IoT Status] --> B[View Current Actuator State]
    B --> C{Control Mode?}

    C -->|Manual| D[User Adjusts Sliders]
    C -->|Automatic| E[System Auto-Control]
    C -->|Fuzzy| F[Fuzzy Logic Control]

    D --> G[Submit Command]
    G --> H[Publish to MQTT]
    H --> I[ESP32 Receives]
    I --> J[Update Actuator]
    J --> K[ESP32 Sends ACK]
    K --> L[Update Database]
    L --> M[Update UI]

    E --> N[Monitor Thresholds]
    N --> O[Auto Adjust]
    O --> J

    F --> P[Calculate Fuzzy Output]
    P --> Q[Apply Control]
    Q --> J
```

### Camera Streaming Flow

```mermaid
sequenceDiagram
    participant U as User
    participant W as Web App
    participant L as Laravel Backend
    participant E as ESP32-CAM

    U->>W: Click "Start Stream"
    W->>L: POST /camera/{id}/stream/start
    L->>E: Publish MQTT Command
    E->>E: Start MJPEG Stream
    E-->>L: ACK Command
    L-->>W: Stream Started

    loop Streaming
        E->>W: MJPEG Frame Data
        W->>U: Display Frame
    end

    U->>W: Click "Capture"
    W->>L: POST /camera/{id}/capture
    L->>E: Capture Command
    E->>E: Take Photo
    E->>L: Upload Image (HTTP POST)
    L->>L: Save to Storage
    L->>L: Insert to camera_images
    L-->>W: Image Saved
    W->>U: Show Success

    U->>W: Click "Stop Stream"
    W->>L: POST /camera/{id}/stream/stop
    L->>E: Stop Command
    E->>E: Stop Streaming
    E-->>L: ACK
    L-->>W: Stream Stopped
```

### Notification Alert Flow

```mermaid
flowchart TB
    A[New Sensor Data Arrives] --> B{Check Thresholds}
    B -->|Within Range| C[Continue Monitoring]
    B -->|Violated| D{Cooldown Expired?}

    D -->|No| E[Skip Alert]
    D -->|Yes| F{Notification Enabled?}

    F -->|No| G[Log Only]
    F -->|Yes| H{Channel?}

    H -->|Telegram| I[Send Telegram]
    H -->|FCM| J[Send Push Notification]
    H -->|Both| K[Send to All Channels]

    I --> L[Update Last Alert Time]
    J --> L
    K --> L

    L --> M[Insert Notification Log]
    M --> N{Success?}

    N -->|Yes| O[Status: Sent]
    N -->|No| P[Status: Failed]
    P --> Q[Log Error Message]
```

## Progressive Web App (PWA) Flow

```mermaid
graph TD
    A[User Visits App] --> B{First Visit?}
    B -->|Yes| C[Load App Shell]
    C --> D[Cache Assets]
    D --> E[Register Service Worker]
    E --> F[Request FCM Permission]

    B -->|No| G[Load from Cache]
    G --> H[Check for Updates]
    H --> I{Update Available?}
    I -->|Yes| J[Download Update]
    I -->|No| K[Use Cached Version]

    F --> L{Permission Granted?}
    L -->|Yes| M[Get FCM Token]
    M --> N[Register Token to Server]
    N --> O[Enable Push Notifications]

    L -->|No| P[Use Without Notifications]

    J --> K
    O --> Q[App Ready]
    P --> Q
```

## Data Flow Architecture

```mermaid
graph LR
    subgraph ESP32 Devices
        S1[DHT22 Sensor]
        S2[MQ-135 Sensor]
        S3[Water Level]
        S4[Soil Moisture]
        S5[Load Cell]
        S6[ESP32-CAM]

        A1[Fan]
        A2[Heater]
        A3[Humidifier]
    end

    subgraph MQTT Broker
        PUB[Publisher Topics]
        SUB[Subscriber Topics]
    end

    subgraph Laravel Backend
        MQTTS[MQTT Subscriber]
        DB[(MySQL Database)]
        MQTTP[MQTT Publisher]
        API[REST API]
        QUEUE[Queue Worker]
    end

    subgraph Frontend
        DASH[Dashboard]
        CHARTS[Real-time Charts]
        CAMERA[Camera View]
    end

    subgraph External Services
        TG[Telegram Bot]
        FCM[Firebase FCM]
    end

    S1 --> PUB
    S2 --> PUB
    S3 --> PUB
    S4 --> PUB
    S5 --> PUB
    S6 --> API

    PUB --> MQTTS
    MQTTS --> DB
    DB --> API
    API --> DASH
    API --> CHARTS
    API --> CAMERA

    DASH --> MQTTP
    MQTTP --> SUB
    SUB --> A1
    SUB --> A2
    SUB --> A3

    DB --> QUEUE
    QUEUE --> TG
    QUEUE --> FCM
```

## Access Control Matrix

| Page/Feature         | Guest  | User   | Admin  | Notes                        |
| -------------------- | ------ | ------ | ------ | ---------------------------- |
| Login Page           | ✅     | ❌     | ❌     | Redirect if authenticated    |
| Register Page        | ✅     | ❌     | ❌     | Redirect if authenticated    |
| IoT Status           | ❌     | ✅     | ✅     | Default landing page         |
| Devices (Own)        | ❌     | ✅     | ✅     | User sees only owned devices |
| Live Camera          | ❌     | ✅     | ✅     | Stream own cameras           |
| Notifications        | ❌     | ✅     | ✅     | Personal settings            |
| Historical Data      | ❌     | ✅     | ✅     | Own device data              |
| Manage Users         | ❌     | ❌     | ✅     | Admin only - CRUD users      |
| Manage Devices (All) | ❌     | ❌     | ✅     | Admin only - Global access   |
| API: Send Commands   | ❌     | ✅     | ✅     | Own devices only (user)      |
| API: FCM Tokens      | ❌     | ✅     | ✅     | Personal tokens              |
| API: Camera Upload   | Public | Public | Public | For ESP32 (token auth)       |

## Responsive Breakpoints

### Mobile Menu (< 768px)

```
┌─────────────────┐
│ ☰ Menu     👤   │ ← Hamburger + User Avatar
├─────────────────┤
│                 │
│  Content Area   │
│                 │
│                 │
└─────────────────┘

On Menu Click:
┌─────────────────┐
│ [Overlay Active]│
│ ┌─────────────┐ │
│ │ ⚡ Menu  [X]│ │ ← Sidebar slides in
│ │─────────────│ │
│ │ 🔌 IoT     │ │
│ │ 📱 Devices │ │
│ │ 📹 Camera  │ │
│ │ 🔔 Notifs  │ │
│ │ 📊 History │ │
│ └─────────────┘ │
└─────────────────┘
```

### Desktop Menu (≥ 768px)

```
┌──────────┬─────────────────────────┐
│ ⚡ Menu  │  Navbar with Title      │
│──────────│─────────────────────────│
│ 🔌 IoT   │                         │
│ 📱 Device│   Content Area          │
│ 📹 Camera│                         │
│ 🔔 Notif │                         │
│ 📊 Chart │                         │
│          │                         │
│ (Admin)  │                         │
│ 👥 Users │                         │
│ 🔧 Mgmt  │                         │
│──────────│                         │
│ 🚪 Logout│                         │
└──────────┴─────────────────────────┘
```

## State Management

### Application States

```mermaid
stateDiagram-v2
    [*] --> Unauthenticated
    Unauthenticated --> Authenticated: Login Success
    Authenticated --> Unauthenticated: Logout

    Authenticated --> Dashboard
    Dashboard --> Monitoring
    Dashboard --> DeviceManagement
    Dashboard --> CameraView
    Dashboard --> NotificationSettings
    Dashboard --> Analytics

    state Authenticated {
        [*] --> User
        User --> Admin: Promote to Admin
        Admin --> User: Demote
    }

    state Monitoring {
        Offline --> Online: Device Connects
        Online --> Offline: Timeout (5 min)
        Online --> Alerting: Threshold Exceeded
        Alerting --> Online: Back to Normal
    }

    state DeviceManagement {
        Idle --> Adding: Click Add
        Adding --> Idle: Save/Cancel
        Idle --> Editing: Click Edit
        Editing --> Idle: Update/Cancel
        Idle --> Deleting: Click Delete
        Deleting --> Idle: Confirm/Cancel
    }
```

## Error Handling Flow

```mermaid
flowchart TD
    A[User Action] --> B{Valid Request?}
    B -->|No| C[Validation Error]
    C --> D[Show Error Messages]
    D --> E[Return to Form]

    B -->|Yes| F[Process Request]
    F --> G{Success?}

    G -->|Yes| H[Success Response]
    H --> I[Show Success Message]
    I --> J[Redirect/Update UI]

    G -->|No| K{Error Type?}
    K -->|403| L[Show Unauthorized]
    K -->|404| M[Show Not Found]
    K -->|500| N[Show Server Error]
    K -->|Network| O[Show Connection Error]

    L --> P[Redirect to Safe Page]
    M --> P
    N --> Q[Log Error]
    O --> R[Retry Option]

    Q --> P
```

## Summary

### Key Navigation Patterns

1. **Default Route**: Authenticated users → IoT Status
2. **Admin Access**: Conditional menu items based on role
3. **Breadcrumb**: Not implemented (flat navigation)
4. **Mobile**: Hamburger menu with overlay
5. **Desktop**: Fixed sidebar navigation

### Performance Optimization

-   ⚡ Route caching (`php artisan route:cache`)
-   🔄 AJAX data loading (no full page reload)
-   📦 Asset bundling with Vite
-   💾 Database query optimization (indexes)
-   🎨 CSS/JS minification in production

### Security Layers

1. **Authentication**: Laravel Sanctum/Session
2. **Authorization**: Middleware (auth, admin)
3. **CSRF Protection**: All POST/PUT/DELETE
4. **Input Validation**: Form Requests
5. **XSS Prevention**: Blade escaping
6. **SQL Injection**: ORM/Query Builder

---

**Page Count**: 15+ unique pages  
**Route Count**: 40+ defined routes  
**User Flows**: 8 documented flows  
**Access Levels**: 3 (Guest, User, Admin)
