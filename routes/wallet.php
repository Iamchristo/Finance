<?php

declare(strict_types=1);

use App\Controllers\Wallet\WalletController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\RateLimitMiddleware;

$router->group(['prefix' => '/wallet', 'middleware' => [AuthMiddleware::class]], function ($router): void {
    $router->get('', [WalletController::class, 'overview']);
    $router->get('/transfer', [WalletController::class, 'transferForm']);
    $router->get('/ledger', [WalletController::class, 'ledger']);

    $router->post('/transfer', [
        'controller' => WalletController::class,
        'action' => 'transfer',
        'middleware' => [CsrfMiddleware::class, RateLimitMiddleware::class . ':wallet_transfer'],
    ]);
});
