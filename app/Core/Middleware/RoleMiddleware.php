<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class RoleMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next, string ...$args): Response
    {
        $role = Session::get('role');

        if ($role === null) {
            return Response::redirect('/login');
        }

        if (!in_array($role, $args, true)) {
            return Response::html('403 Forbidden', 403);
        }

        return $next($request);
    }
}
