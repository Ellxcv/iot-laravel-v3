<?php

return [
    /**
     * MQTT Host
     */
    'host' => env('MQTT_HOST', 'localhost'),
    
    /**
     * MQTT Port
     */
    'port' => env('MQTT_PORT', 1883),
    
    /**
     * MQTT Client ID
     */
    'client_id' => env('MQTT_CLIENT_ID', 'laravel_iot'),
    
    /**
     * Authentication
     */
    'auth' => [
        'username' => env('MQTT_AUTH_USERNAME'),
        'password' => env('MQTT_AUTH_PASSWORD'),
    ],
    
    /**
     * Connection settings
     */
    'clean_session' => env('MQTT_CLEAN_SESSION', true),
    'keepalive' => env('MQTT_KEEPALIVE', 60),
    'connection_timeout' => env('MQTT_CONNECTION_TIMEOUT', 5),
    
    /**
     * TLS Settings
     */
    'tls' => [
        'enabled' => env('MQTT_TLS_ENABLED', false),
        'verify_peer' => env('MQTT_TLS_VERIFY_PEER', true),
        'verify_peer_name' => env('MQTT_TLS_VERIFY_PEER_NAME', true),
        'ca_file' => env('MQTT_TLS_CA_FILE'),
    ],
    
    /**
     * Topic settings
     */
    'topic_prefix' => env('MQTT_TOPIC_PREFIX', 'iot/devices'),
    'qos' => env('MQTT_QOS', 1),
    
    /**
     * Logging
     */
    'logging_enabled' => env('MQTT_LOGGING_ENABLED', true),
];
