<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Finance Platform'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    'url' => env('APP_URL', 'http://localhost'),
    'key' => env('APP_KEY', ''),
    'timezone' => 'UTC',
    'simulated_banner' => env(
        'SIMULATED_PLATFORM_BANNER',
        'Simulated environment — all balances, prices, and trades are virtual.'
    ),
];
