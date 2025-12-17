<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', '8328144620:AAHpocFB_3acp-NZV6Gi4bfP5M9rT8HYfJI'),
    ],

    'firebase' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'project_id' => env('FCM_PROJECT_ID', 'iot-laravel-6c139'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '814456771799'),
        'app_id' => env('FIREBASE_APP_ID', '1:814456771799:web:0b6926f649ab9a5279cc92'),
        'vapid_public' => env('FIREBASE_VAPID_PUBLIC', 'BBo00ecklx75r4leDlsDfl3_WQ7X4y8Msv5m9AJ4SwH27UCGUiaHDyBesw7U48A6wRCWtNWZP_gSOxzIeLerMOU'),
        'api_key' => env('FIREBASE_API_KEY', 'AIzaSyCjCKE7SPsDGQudmU3QY3sxmwQsxW9yF6A'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN', 'iot-laravel-6c139.firebaseapp.com'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', 'iot-laravel-6c139.firebasestorage.app'),
    ],

];
