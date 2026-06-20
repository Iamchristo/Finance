<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');

        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf_token', $token);
        }

        return $token;
    }

    public static function verify(?string $submitted): bool
    {
        $token = Session::get('_csrf_token');

        return is_string($token) && is_string($submitted) && hash_equals($token, $submitted);
    }
}
