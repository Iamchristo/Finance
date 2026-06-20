<?php

declare(strict_types=1);

use App\Controllers\Dashboard\RealEstateDashboardController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CsrfMiddleware;

$router->group(['prefix' => '/dashboard/realestate', 'middleware' => [AuthMiddleware::class]], function ($router): void {
    $router->get('', [RealEstateDashboardController::class, 'overview']);
    $router->get('/properties', [RealEstateDashboardController::class, 'properties']);
    $router->get('/portfolio', [RealEstateDashboardController::class, 'portfolio']);
    $router->get('/marketplace', [RealEstateDashboardController::class, 'marketplace']);
    $router->get('/listings', [RealEstateDashboardController::class, 'listings']);

    $router->post('/invest', [
        'controller' => RealEstateDashboardController::class,
        'action' => 'invest',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->post('/marketplace/offer', [
        'controller' => RealEstateDashboardController::class,
        'action' => 'makeOffer',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->post('/listings', [
        'controller' => RealEstateDashboardController::class,
        'action' => 'createListing',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->post('/listings/offers/accept', [
        'controller' => RealEstateDashboardController::class,
        'action' => 'acceptOffer',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->post('/listings/offers/reject', [
        'controller' => RealEstateDashboardController::class,
        'action' => 'rejectOffer',
        'middleware' => [CsrfMiddleware::class],
    ]);
});
