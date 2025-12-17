# Reusable Components - Documentation

## Overview

Created reusable Blade components untuk menghindari code duplication. Sekarang setiap view tinggal pakai components yang sudah jadi.

---

## Created Components

### 1. Layout Component (`x-layout`)

**File:** `resources/views/components/layout.blade.php`

**Purpose:** Main layout untuk semua pages (sidebar + navbar + content area)

**Usage:**
```blade
<x-layout title="Page Title" active="menu-key">
    {{-- Main content here --}}
</x-layout>
```

**Props:**
- `title` - Page title (default: "Dashboard")
- `active` - Active menu key untuk highlighting sidebar

**Slots:**
- Default slot - Main page content
- `head` - Additional `<head>` content (CSS, meta tags, etc.)
- `navbarSlot` - Content for navbar right side (badges, buttons)
- `scripts` - Page-specific JavaScript

**Features:**
- ✅ Includes sidebar automatically
- ✅ Includes navbar automatically
- ✅ Common scripts (toggleSidebar)
- ✅ CSRF token
- ✅ Vite assets

---

### 2. Sidebar Component (`x-sidebar`)

**File:** `resources/views/components/sidebar.blade.php`

**Purpose:** Navigation sidebar dengan menu links

**Usage:**
```blade
<x-sidebar active="dashboard" />
```

**Props:**
- `active` - Active menu item (`dashboard`, `iot`, `camera`, `notifications`, `history`)

**Features:**
- ✅ Responsive hamburger menu
- ✅ Active state highlighting
- ✅ Logout button
- ✅ Overlay for mobile
- ✅ Smooth transitions

**Menu Items:**
- Dashboard
- IoT Status
- Live Camera
- Notifications
- Historical Data

---

### 3. Navbar Component (`x-navbar`)

**File:** `resources/views/components/navbar.blade.php`

**Purpose:** Top navigation bar dengan hamburger button

**Usage:**
```blade
<x-navbar title="Historical Data">
    {{-- Optional: Status badge, buttons, etc --}}
    <div class="flex items-center space-x-2">
        <span class="text-white">Online</span>
    </div>
</x-navbar>
```

**Props:**
- `title` - Page title displayed in navbar

**Slots:**
- Default slot - Right side content (badges, buttons, user info)

**Features:**
- ✅ Hamburger menu button
- ✅ Page title
- ✅ Right side slot untuk custom content
- ✅ Glassmorphism design

---

## Migration Guide

### Before (Old Way)
```blade
<!DOCTYPE html>
<html>
<head>
    <title>...</title>
    @vite(...)
</head>
<body>
    <!-- Sidebar (copy-paste 100+ lines) -->
    <aside id="sidebar">...</aside>
    
    <!-- Overlay -->
    <div id="overlay">...</div>
    
    <div id="mainContent">
        <!-- Navbar (copy-paste 30+ lines) -->
        <nav>...</nav>
        
        <main>
            <!-- Your content -->
        </main>
    </div>
    
    <script>
        function toggleSidebar() {...}
    </script>
</body>
</html>
```

### After (New Way)
```blade
<x-layout title="Your Page" active="menu-key">
    <!-- Your content (just 10-20 lines) -->
</x-layout>
```

**Reduction:** ~150 lines → ~5 lines boilerplate!

---

## Complete Examples

### Example 1: Simple Page
```blade
<x-layout title="IoT Status" active="iot">
    <div class="space-y-8">
        <h1>IoT Device Status</h1>
        <!-- Your content -->
    </div>
</x-layout>
```

### Example 2: Page with Extra Scripts
```blade
<x-layout title="Historical Data" active="history">
    {{-- Extra head content --}}
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    {{-- Main content --}}
    <div>
        <canvas id="chart"></canvas>
    </div>

    {{-- Page scripts --}}
    <x-slot name="scripts">
        <script>
            const ctx = document.getElementById('chart');
            new Chart(ctx, {...});
        </script>
    </x-slot>
</x-layout>
```

### Example 3: Page with Navbar Badge
```blade
<x-layout title="Notifications" active="notifications">
    {{-- Navbar badge --}}
    <x-slot name="navbarSlot">
        @if($settings && $settings->canSendNotifications())
            <div class="badge badge-success">Enabled</div>
        @else
            <div class="badge badge-gray">Disabled</div>
        @endif
    </x-slot>

    {{-- Main content --}}
    <div>Your notification settings...</div>
</x-layout>
```

---

## Benefits

### 1. DRY Principle ✅
- No more copy-paste sidebar (was 100+ lines per file)
- No more copy-paste navbar (was 30+ lines per file)
- No more copy-paste scripts

### 2. Consistency ✅
- All pages use same sidebar
- Single source of truth for navigation
- Easy to add new menu items

### 3. Maintainability ✅
- Update sidebar once → affects all pages
- Add new menu item → update 1 file
- Fix bug → fix once

### 4. Clean Code ✅
- Views focus on content, not layout
- Separation of concerns
- Easier to read and understand

---

## File Structure

```
resources/views/
├── components/
│   ├── layout.blade.php       # Main layout wrapper
│   ├── sidebar.blade.php      # Sidebar navigation
│   └── navbar.blade.php       # Top navbar
├── dashboard.blade.php         # Use <x-layout>
├── iot/
│   └── status.blade.php        # Use <x-layout>
├── camera/
│   └── live.blade.php          # Use <x-layout>
├── notification/
│   └── index.blade.php         # Use <x-layout>
└── history/
    └── index.blade.php         # Use <x-layout>
```

---

## Active State Mapping

| Page | Active Value |
|------|-------------|
| Dashboard | `dashboard` |
| IoT Status | `iot` |
| Live Camera | `camera` |
| Notifications | `notifications` |
| Historical Data | `history` |

---

## To Do: Refactor Existing Views

**Update these files to use new components:**
- [ ] `resources/views/dashboard.blade.php`
- [ ] `resources/views/iot/status.blade.php`
- [ ] `resources/views/camera/live.blade.php`
- [ ] `resources/views/notification/index.blade.php`
- [ ] `resources/views/history/index.blade.php`

**Steps for each file:**
1. Replace everything with `<x-layout>`
2. Move content to main slot
3. Move extra scripts to `scripts` slot
4. Move extra head to `head` slot
5. Set correct `active` prop

---

## Future Enhancements

### Potential Additional Components

1. **Stats Card Component**
   ```blade
   <x-stat-card title="Temperature" value="25°C" icon="thermometer" />
   ```

2. **Data Table Component**
   ```blade
   <x-data-table :headers="[...]" :data="[...]" />
   ```

3. **Chart Component**
   ```blade
   <x-chart type="line" :data="[...]" />
   ```

4. **Filter Panel Component**
   ```blade
   <x-filter-panel>
       <x-slot name="filters">...</x-slot>
   </x-filter-panel>
   ```

---

## Summary

✅ **Created 3 Core Components:**
1. `x-layout` - Main wrapper
2. `x-sidebar` - Navigation
3. `x-navbar` - Top bar

✅ **Benefits:**
- 90% less boilerplate code
- Single source of truth
- Easy maintenance
- Consistent design

✅ **Next Step:**
Refactor existing 5 views to use new components!
