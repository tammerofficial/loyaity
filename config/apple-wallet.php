<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Apple Wallet Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Apple Wallet pass generation
    |
    */

    'team_id' => env('APPLE_WALLET_TEAM_ID', ''),
    'pass_type_id' => env('APPLE_WALLET_PASS_TYPE_ID', 'pass.com.example.loyalty'),
    'certificate_path' => env('APPLE_WALLET_CERTIFICATE_PATH', ''),
    'certificate_password' => env('APPLE_WALLET_CERTIFICATE_PASSWORD', ''),
    'wwdr_certificate_path' => env('APPLE_WALLET_WWDR_CERTIFICATE_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Pass Configuration
    |--------------------------------------------------------------------------
    |
    | Default pass appearance and behavior settings
    |
    */

    'organization_name' => env('APP_NAME', 'Loyalty System'),
    'description' => 'Loyalty Card',
    'logo_text' => env('APP_NAME', 'Loyalty System'),
    
    'colors' => [
        'foreground' => 'rgb(255, 255, 255)',
        'background' => 'rgb(0, 122, 255)',
        'label' => 'rgb(255, 255, 255)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Service Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Apple Wallet web service integration
    |
    */

    'web_service_url' => env('APP_URL', 'http://localhost:8000') . '/api/v1/apple-wallet',
    'authentication_required' => true,
];
