<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

final class CsrfMiddleware implements MiddlewareInterface
{
    private const PROTECTED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, callable $next, string ...$args): Response
    {
        if (in_array($request->method(), self::PROTECTED_METHODS, true)) {
            $token = $request->input('_csrf') ?? $request->header('X-CSRF-Token');

            if (!Csrf::verify(is_string($token) ? $token : null)) {
                return Response::html('419 Page Expired (invalid CSRF token)', 419);
            }
        }

        return $next($request);
    }
}
