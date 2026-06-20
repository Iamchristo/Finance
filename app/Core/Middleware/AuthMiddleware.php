<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next, string ...$args): Response
    {
        if (Session::userId() === null) {
            Session::flash('error', 'Please sign in to continue.');

            return Response::redirect('/login');
        }

        return $next($request);
    }
}
