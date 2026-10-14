<?php

/*
|--------------------------------------------------------------------------
| Krishi Sathi application settings
|--------------------------------------------------------------------------
| Values the mobile app needs at runtime are exposed through GET /api/v1/meta,
| so most of them can be changed here (or in .env) without an app update.
*/

return [

    // Who runs this service – shown on the public privacy policy (/privacy)
    // and the account-deletion page (/account/delete). Set both before publishing.
    'operator_name' => env('KRISHI_OPERATOR_NAME', 'Krishi Sathi'),
    'support_email' => env('KRISHI_SUPPORT_EMAIL'),
    'privacy_policy_updated' => '2026-10-14',

    // Free government agriculture call centre (Agriculture Information Service).
    'helpline_number' => env('KRISHI_HELPLINE', '16123'),

    // Land-unit conversions. A "bigha" differs by region; 33 decimal is the
    // most common standard. The app lets users override it.
    'units' => [
        'decimal_per_hectare' => 247.105,
        'decimal_per_acre' => 100,
        'decimal_per_bigha' => (float) env('KRISHI_BIGHA_DECIMAL', 33),
        'decimal_per_katha' => (float) env('KRISHI_BIGHA_DECIMAL', 33) / 20,
    ],

    // Lowest mobile-app build that still works with this API.
    'min_app_version' => env('KRISHI_MIN_APP_VERSION', '1.0.0'),

    // Sanctum tokens for the mobile app expire after this many days.
    'token_ttl_days' => (int) env('KRISHI_TOKEN_TTL_DAYS', 180),

    'weather' => [
        // Open-Meteo: free, no API key. Commercial use needs their paid plan.
        'base_url' => env('WEATHER_BASE_URL', 'https://api.open-meteo.com/v1/forecast'),
        'cache_minutes' => (int) env('WEATHER_CACHE_MINUTES', 60),
        'timeout_seconds' => 8,
    ],

    'uploads' => [
        'max_kb' => 5120,          // accepted upload size
        'max_dimension' => 1280,   // images are resized down to this (px) to save bandwidth
        'jpeg_quality' => 75,
    ],
];
