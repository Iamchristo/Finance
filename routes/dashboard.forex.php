<?php

declare(strict_types=1);

use App\Controllers\Dashboard\ForexDashboardController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\RateLimitMiddleware;

$router->group(['prefix' => '/dashboard/forex', 'middleware' => [AuthMiddleware::class]], function ($router): void {
    $router->get('', [ForexDashboardController::class, 'overview']);
    $router->get('/markets', [ForexDashboardController::class, 'markets']);
    $router->get('/trade/{symbol}', [ForexDashboardController::class, 'trade']);
    $router->get('/positions', [ForexDashboardController::class, 'positions']);
    $router->get('/orders', [ForexDashboardController::class, 'orderHistory']);
    $router->get('/watchlist', [ForexDashboardController::class, 'watchlist']);
    $router->get('/strategies', [ForexDashboardController::class, 'strategies']);

    $router->post('/order', [
        'controller' => ForexDashboardController::class,
        'action' => 'placeOrder',
        'middleware' => [CsrfMiddleware::class, RateLimitMiddleware::class . ':order_place'],
    ]);

    $router->post('/positions/close', [
        'controller' => ForexDashboardController::class,
        'action' => 'closePosition',
        'middleware' => [CsrfMiddleware::class, RateLimitMiddleware::class . ':order_place'],
    ]);

    $router->post('/watchlist/add', [
        'controller' => ForexDashboardController::class,
        'action' => 'addToWatchlist',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->post('/watchlist/remove', [
        'controller' => ForexDashboardController::class,
        'action' => 'removeFromWatchlist',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->post('/strategies/subscribe', [
        'controller' => ForexDashboardController::class,
        'action' => 'subscribeStrategy',
        'middleware' => [CsrfMiddleware::class],
    ]);
});
