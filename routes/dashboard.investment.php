<?php

declare(strict_types=1);

use App\Controllers\Dashboard\InvestmentDashboardController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CsrfMiddleware;

$router->group(['prefix' => '/dashboard/investment', 'middleware' => [AuthMiddleware::class]], function ($router): void {
    $router->get('', [InvestmentDashboardController::class, 'overview']);
    $router->get('/plans', [InvestmentDashboardController::class, 'plans']);
    $router->get('/subscriptions', [InvestmentDashboardController::class, 'subscriptions']);
    $router->get('/referrals', [InvestmentDashboardController::class, 'referrals']);

    $router->post('/subscribe', [
        'controller' => InvestmentDashboardController::class,
        'action' => 'subscribe',
        'middleware' => [CsrfMiddleware::class],
    ]);
});
