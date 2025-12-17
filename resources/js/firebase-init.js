// Firebase Cloud Messaging Frontend Integration
// This file handles Firebase initialization, token generation, and foreground notifications

import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js';
import { getMessaging, getToken, onMessage } from 'https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging.js';

// Firebase configuration (from backend config)
const firebaseConfig = {
    apiKey: window.FIREBASE_CONFIG?.apiKey || "AIzaSyCjCKE7SPsDGQudmU3QY3sxmwQsxW9yF6A",
    authDomain: window.FIREBASE_CONFIG?.authDomain || "iot-laravel-6c139.firebaseapp.com",
    projectId: window.FIREBASE_CONFIG?.projectId || "iot-laravel-6c139",
    storageBucket: window.FIREBASE_CONFIG?.storageBucket || "iot-laravel-6c139.firebasestorage.app",
    messagingSenderId: window.FIREBASE_CONFIG?.messagingSenderId || "814456771799",
    appId: window.FIREBASE_CONFIG?.appId || "1:814456771799:web:0b6926f649ab9a5279cc92"
};

// VAPID public key for FCM  
const vapidKey = window.FIREBASE_CONFIG?.vapidPublic || 'BBo00ecklx75r4leDlsDfl3_WQ7X4y8Msv5m9AJ4SwH27UCGUiaHDyBesw7U48A6wRCWtNWZP_gSOxzIeLerMOU';

let app;
let messaging;
let currentToken = null;

// Initialize Firebase
export function initializeFirebase() {
    try {
        console.log('[Firebase] Initializing...');
        app = initializeApp(firebaseConfig);
        messaging = getMessaging(app);
        console.log('[Firebase] Initialized successfully');
        return true;
    } catch (error) {
        console.error('[Firebase] Initialization error:', error);
        return false;
    }
}

// Request notification permission
export async function requestNotificationPermission() {
    try {
        const permission = await Notification.requestPermission();
        console.log('[Firebase] Notification permission:', permission);

        if (permission === 'granted') {
            console.log('[Firebase] Notification permission granted');
            return true;
        } else {
            console.log('[Firebase] Notification permission denied');
            return false;
        }
    } catch (error) {
        console.error('[Firebase] Permission request error:', error);
        return false;
    }
}

// Get FCM token
export async function getFCMToken() {
    try {
        if (!messaging) {
            console.error('[Firebase] Messaging not initialized');
            return null;
        }

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            console.log('[Firebase] Permission not granted, cannot get token');
            return null;
        }

        // Register service worker first
        const registration = await registerServiceWorker();
        if (!registration) {
            console.error('[Firebase] Service worker not registered');
            return null;
        }

        const token = await getToken(messaging, {
            vapidKey: vapidKey,
            serviceWorkerRegistration: registration
        });

        if (token) {
            console.log('[Firebase] FCM Token:', token);
            currentToken = token;
            return token;
        } else {
            console.log('[Firebase] No registration token available');
            return null;
        }
    } catch (error) {
        console.error('[Firebase] Error getting token:', error);
        return null;
    }
}

// Register service worker
async function registerServiceWorker() {
    try {
        if ('serviceWorker' in navigator) {
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            console.log('[Firebase] Service Worker registered:', registration);
            await navigator.serviceWorker.ready;
            return registration;
        } else {
            console.error('[Firebase] Service Worker not supported');
            return null;
        }
    } catch (error) {
        console.error('[Firebase] Service Worker registration error:', error);
        return null;
    }
}

// Handle foreground messages
export function initForegroundMessageHandler(callback) {
    if (!messaging) {
        console.error('[Firebase] Messaging not initialized');
        return;
    }

    onMessage(messaging, (payload) => {
        console.log('[Firebase] Foreground message received:', payload);

        const notificationTitle = payload.notification?.title || 'IoT Alert';
        const notificationOptions = {
            body: payload.notification?.body || 'New notification',
            icon: payload.notification?.icon || '/icons/icon-192.png',
            badge: '/icons/icon-192.png',
            tag: 'iot-notification',
            requireInteraction: false,
            data: payload.data
        };

        // Show notification
        if (Notification.permission === 'granted') {
            new Notification(notificationTitle, notificationOptions);
        }

        // Execute callback if provided
        if (callback && typeof callback === 'function') {
            callback(payload);
        }
    });

    console.log('[Firebase] Foreground message handler initialized');
}

// Auto-initialize when script loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeFirebase();
    });
} else {
    initializeFirebase();
}

// Export current token getter
export function getCurrentToken() {
    return currentToken;
}

// Global access
window.FirebaseNotification = {
    initialize: initializeFirebase,
    requestPermission: requestNotificationPermission,
    getToken: getFCMToken,
    getCurrentToken: getCurrentToken,
    onForegroundMessage: initForegroundMessageHandler
};

console.log('[Firebase] Module loaded');
