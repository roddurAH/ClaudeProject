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

    // AI crop doctor (photo + question → instant advice). See docs/AI_DOCTOR.md.
    'ai' => [
        'enabled' => (bool) env('KRISHI_AI_ENABLED', true),
        // "anthropic" = Claude; "demo" = canned answers for trying the app without an API key.
        'driver' => env('KRISHI_AI_DRIVER', 'anthropic'),
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('KRISHI_AI_MODEL', 'claude-opus-5-5'),
        'effort' => env('KRISHI_AI_EFFORT', 'medium'),     // low | medium | high
        'max_tokens' => (int) env('KRISHI_AI_MAX_TOKENS', 16000),
        'timeout_seconds' => (int) env('KRISHI_AI_TIMEOUT', 120),
        'daily_limit' => (int) env('KRISHI_AI_DAILY_LIMIT', 10), // AI questions per user per day
        // USD per million tokens, only for the cost estimate in the admin panel.
        'prices' => [
            'claude-opus-5-5' => ['input' => 4.00, 'output' => 20.00, 'cache_read' => 0.20, 'cache_write' => 5.00],
            'claude-sonnet-5-5' => ['input' => 2.00, 'output' => 10.00, 'cache_read' => 0.20, 'cache_write' => 2.50],
            'claude-haiku-5-5' => ['input' => 0.10, 'output' => 0.50, 'cache_read' => 0.01, 'cache_write' => 0.125],
        ],
    ],

    'uploads' => [
        'max_kb' => 5120,          // accepted upload size
        'max_dimension' => 1280,   // images are resized down to this (px) to save bandwidth
        'jpeg_quality' => 75,
    ],
];
