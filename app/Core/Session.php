<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $path = Config::get('session.path');
        if (is_string($path) && !is_dir($path)) {
            mkdir($path, 0700, true);
        }
        if (is_string($path)) {
            session_save_path($path);
        }

        session_name((string) Config::get('session.name', 'finance_session'));

        session_set_cookie_params([
            'lifetime' => (int) Config::get('session.lifetime_minutes', 120) * 60,
            'path' => '/',
            'domain' => '',
            'secure' => (bool) Config::get('session.secure_cookie', false),
            'httponly' => true,
            'samesite' => (string) Config::get('session.same_site', 'Lax'),
        ]);

        session_start();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function userId(): ?int
    {
        $id = self::get('user_id');

        return $id !== null ? (int) $id : null;
    }
}
