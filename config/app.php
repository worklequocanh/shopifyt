<?php

/**
 * Application Configuration
 */

return [
    // Application settings
    'app_name' => 'Shopify E-commerce',
    'app_env' => 'development', // development, production
    
    // Base URL
    'base_url' => 'http://localhost:8080',
    
    // Timezone
    'timezone' => 'Asia/Ho_Chi_Minh',
    
    // Session configuration
    'session' => [
        'name' => 'PHPSESSID',
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'secure' => false,
        'httponly' => true
    ],
    
    // Error reporting
    'debug' => true,
    'display_errors' => true,
    
    // Pagination
    'items_per_page' => 12,
];
