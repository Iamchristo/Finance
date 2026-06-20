<?php

declare(strict_types=1);

return [
    'rate_limits' => [
        // key => [max attempts, decay window in seconds]
        'login' => [5, 300],
        'register' => [5, 600],
        'password_reset' => [5, 600],
        'order_place' => [30, 60],
        'wallet_transfer' => [10, 60],
    ],
    'password_min_length' => 10,
    'two_factor_code_ttl_seconds' => 300,
];
