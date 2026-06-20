<?php

declare(strict_types=1);

return [
    'host' => env('MAIL_HOST', 'localhost'),
    'port' => (int) env('MAIL_PORT', 1025),
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
    'from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@example.test'),
    'from_name' => env('MAIL_FROM_NAME', 'Finance Platform'),
];
