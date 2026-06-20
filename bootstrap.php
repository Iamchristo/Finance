<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Core\Config;
use Dotenv\Dotenv;

if (is_file(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->load();
}

date_default_timezone_set('UTC');

Config::load(__DIR__ . '/config');
