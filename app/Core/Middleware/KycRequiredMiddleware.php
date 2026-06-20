<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\UserRepository;

/**
 * Not currently attached to any route — KYC submission is scaffolded
 * (kyc_submissions table + admin review queue) but gating dashboard access
 * behind approval is left as a config-driven follow-up so the demo remains
 * usable immediately after registration.
 */
final class KycRequiredMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function handle(Request $request, callable $next, string ...$args): Response
    {
        $userId = Session::userId();
        $user = $userId !== null ? $this->users->findById($userId) : null;

        if ($user === null || $user->kycStatus !== 'approved') {
            Session::flash('error', 'This feature requires KYC verification.');

            return Response::redirect('/wallet');
        }

        return $next($request);
    }
}
