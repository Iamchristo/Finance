<?php

declare(strict_types=1);

return [
    'name' => env('SESSION_NAME', 'finance_session'),
    'lifetime_minutes' => (int) env('SESSION_LIFETIME', 120),
    'secure_cookie' => filter_var(env('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOL),
    'same_site' => 'Lax',
    'path' => __DIR__ . '/../storage/sessions',
];
