<?php

declare(strict_types=1);

use App\Controllers\Auth\AuthController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\GuestOnlyMiddleware;
use App\Core\Middleware\RateLimitMiddleware;

$router->group(['middleware' => [GuestOnlyMiddleware::class]], function ($router): void {
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->get('/login', [AuthController::class, 'showLogin']);
});

$router->post('/register', [
    'controller' => AuthController::class,
    'action' => 'register',
    'middleware' => [CsrfMiddleware::class, RateLimitMiddleware::class . ':register'],
]);

$router->post('/login', [
    'controller' => AuthController::class,
    'action' => 'login',
    'middleware' => [CsrfMiddleware::class, RateLimitMiddleware::class . ':login'],
]);

$router->post('/logout', [
    'controller' => AuthController::class,
    'action' => 'logout',
    'middleware' => [AuthMiddleware::class, CsrfMiddleware::class],
]);
