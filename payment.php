<?php
// config/payment.php

return [
    // Application Mode
    'mode' => 'demo', // 'demo' atau 'production'
    
    // Payment Gateway Configuration
    'payment_gateway' => [
        'demo' => [
            'name' => 'Demo Gateway',
            'qr_endpoint' => 'https://api.qrserver.com/v1/create-qr-code/',
            'callback_url' => 'https://yourdomain.com/api/callback.php'
        ],
        'production' => [
            'name' => 'Tripay',
            'api_key' => 'your_api_key_here',
            'private_key' => 'your_private_key',
            'merchant_code' => 'your_merchant_code',
            'callback_url' => 'https://yourdomain.com/api/callback.php'
        ]
    ],
    
    // Application Settings
    'app' => [
        'name' => 'Panel Payment QRIS',
        'version' => '1.0.0',
        'timezone' => 'Asia/Jakarta',
        'currency' => 'IDR',
        'admin_email' => 'admin@example.com'
    ],
    
    // QRIS Settings
    'qris' => [
        'size' => '300x300',
        'format' => 'png',
        'expiry_minutes' => 5,
        'check_interval' => 5 // seconds
    ],
    
    // Security
    'security' => [
        'session_timeout' => 3600,
        'max_login_attempts' => 5,
        'allowed_ips' => [] // kosongkan untuk semua IP
    ]
];