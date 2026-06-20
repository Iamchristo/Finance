<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    /** @var array<string, array<string, mixed>> */
    private static array $items = [];

    private static bool $loaded = false;

    public static function load(string $configPath): void
    {
        if (self::$loaded) {
            return;
        }

        foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            self::$items[$key] = require $file;
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$items[$segments[0]] ?? null;

        foreach (array_slice($segments, 1) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value ?? $default;
    }
}
