<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false ? $default : $value;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return \App\Core\Config::get($key, $default);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage') . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('money')) {
    function money(string|float $amount, string $currency = 'USD'): string
    {
        return $currency . ' ' . number_format((float) $amount, 2);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        return e($_SESSION['_old_input'][$key] ?? $default);
    }
}

if (!function_exists('vite_asset')) {
    function vite_asset(string $entry): string
    {
        static $manifest = null;

        $manifestPath = base_path('public/build/.vite/manifest.json');

        if ($manifest === null) {
            $manifest = is_file($manifestPath)
                ? json_decode(file_get_contents($manifestPath), true)
                : [];
        }

        if (isset($manifest[$entry]['file'])) {
            return '/build/' . $manifest[$entry]['file'];
        }

        // Dev fallback when assets haven't been built yet.
        return '/' . $entry;
    }
}
