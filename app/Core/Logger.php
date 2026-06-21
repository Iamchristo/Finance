<?php

declare(strict_types=1);

namespace App\Core;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as Monolog;
use Throwable;

final class Logger
{
    private static ?Monolog $instance = null;

    public static function instance(): Monolog
    {
        if (self::$instance === null) {
            self::$instance = new Monolog('app');
            self::$instance->pushHandler(new StreamHandler(storage_path('logs/app.log'), Level::Debug));
        }

        return self::$instance;
    }

    public static function exception(Throwable $e): void
    {
        self::instance()->error($e->getMessage(), [
            'exception' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
