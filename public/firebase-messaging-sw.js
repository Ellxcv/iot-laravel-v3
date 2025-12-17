/* public/firebase-messaging-sw.js */

// Service Worker lifecycle
self.addEventListener('install', () => {
    console.log('[SW] Installing...');
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    console.log('[SW] Activating...');
    e.waitUntil(self.clients.claim());
});

// Import Firebase scripts
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js');

// Initialize Firebase
firebase.initializeApp({
    apiKey: "AIzaSyCjCKE7SPsDGQudmU3QY3sxmwQsxW9yF6A",
    authDomain: "iot-laravel-6c139.firebaseapp.com",
    projectId: "iot-laravel-6c139",
    storageBucket: "iot-laravel-6c139.firebasestorage.app",
    messagingSenderId: "814456771799",
    appId: "1:814456771799:web:0b6926f649ab9a5279cc92",
});

const messaging = firebase.messaging();

// Handle background messages (when app is in background or closed)
messaging.onBackgroundMessage((payload) => {
    console.log('[SW] Background message received:', payload);

    const n = payload.notification || {};
    const d = payload.data || {};

    const title = n.title || d.title || 'IoT Panel';
    const options = {
        body: n.body || d.body || 'Ada notifikasi baru',
        icon: n.icon || '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        tag: 'iot-notification', // Prevent duplicate notifications
        requireInteraction: false, // Auto-dismiss after few seconds
        vibrate: [200, 100, 200], // Vibration pattern for Android
        silent: false,

        // Data payload untuk handle klik
        data: {
            click_action: d.click_action || n.click_action || '/dashboard',
            url: d.url || '/dashboard',
            ...d
        },

        // Actions (optional, untuk tombol di notifikasi)
        actions: [
            {
                action: 'open',
                title: 'Buka',
                icon: '/icons/icon-192.png'
            },
            {
                action: 'close',
                title: 'Tutup'
            }
        ]
    };

    console.log('[SW] Showing notification:', title, options);

    return self.registration.showNotification(title, options);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked:', event);

    event.notification.close();

    // Handle action buttons
    if (event.action === 'close') {
        return; // Just close, do nothing
    }

    // Get URL from notification data
    const clickAction = event.notification?.data?.click_action
        || event.notification?.data?.url
        || '/dashboard';

    console.log('[SW] Opening URL:', clickAction);

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then((clientsArr) => {
            console.log('[SW] Found clients:', clientsArr.length);

            // Try to focus existing window with matching URL
            for (const client of clientsArr) {
                const clientUrl = new URL(client.url);
                const targetUrl = new URL(clickAction, self.location.origin);

                // Check if path matches
                if (clientUrl.pathname === targetUrl.pathname && 'focus' in client) {
                    console.log('[SW] Focusing existing window:', client.url);
                    return client.focus();
                }
            }

            // If no matching window, try to focus any window from same origin
            for (const client of clientsArr) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    console.log('[SW] Focusing origin window and navigating:', client.url);
                    client.navigate(clickAction);
                    return client.focus();
                }
            }

            // If no window exists, open new one
            if (clients.openWindow) {
                console.log('[SW] Opening new window:', clickAction);
                return clients.openWindow(clickAction);
            }
        }).catch(err => {
            console.error('[SW] Error handling click:', err);
        })
    );
});

// Handle push event (alternative method, backup)
self.addEventListener('push', (event) => {
    console.log('[SW] Push event received:', event);

    if (!event.data) {
        console.log('[SW] No data in push event');
        return;
    }

    try {
        const payload = event.data.json();
        console.log('[SW] Push payload:', payload);

        const n = payload.notification || {};
        const title = n.title || 'IoT Panel';
        const options = {
            body: n.body || 'Ada notifikasi baru',
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-192.png',
            tag: 'iot-notification',
            data: payload.data || {}
        };

        event.waitUntil(
            self.registration.showNotification(title, options)
        );
    } catch (err) {
        console.error('[SW] Error parsing push data:', err);
    }
});

console.log('[SW] Firebase Messaging Service Worker loaded');
