<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class GuestOnlyMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next, string ...$args): Response
    {
        if (Session::userId() !== null) {
            return Response::redirect('/wallet');
        }

        return $next($request);
    }
}
