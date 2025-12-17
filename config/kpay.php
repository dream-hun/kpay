<?php

return [
    /*
    |--------------------------------------------------------------------------
    | K-Pay Base URL
    |--------------------------------------------------------------------------
    |
    | As per the official docs, all requests are POSTed to the base URL.
    | Docs: https://developers.kpay.africa/documentation.php
    |
    */
    'base_url' => env('KPAY_BASE_URL', 'https://pay.esicia.com/'),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | All API requests require:
    | - Kpay-Key header
    | - Authorization: Basic base64(username:password)
    |
    */
    'api_key' => env('KPAY_API_KEY', ''),
    'username' => env('KPAY_USERNAME', ''),
    'password' => env('KPAY_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Defaults for payment initiation
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'currency' => env('KPAY_CURRENCY', 'RWF'),
        'retailerid' => env('KPAY_RETAILER_ID', ''),
        'returl' => env('KPAY_RETURL', ''),
        'redirecturl' => env('KPAY_REDIRECTURL', ''),
        'logourl' => env('KPAY_LOGOURL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Callback (Webhook) handling
    |--------------------------------------------------------------------------
    */
    'callback' => [
        'enabled' => env('KPAY_CALLBACK_ENABLED', true),
        'path' => env('KPAY_CALLBACK_PATH', 'kpay/callback'),
        // Use 'api' by default so webhooks aren't blocked by CSRF protection.
        'middleware' => env('KPAY_CALLBACK_MIDDLEWARE', 'api'),
    ],
];
