<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Config;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;

final class RateLimitMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next, string ...$args): Response
    {
        $bucket = $args[0] ?? 'default';
        [$max, $decaySeconds] = Config::get("security.rate_limits.{$bucket}", [60, 60]);
        $key = $bucket . ':' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, (int) $max, (int) $decaySeconds)) {
            return Response::html('429 Too Many Requests — please slow down and try again shortly.', 429);
        }

        RateLimiter::hit($key);

        return $next($request);
    }
}
