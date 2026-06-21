<?php

declare(strict_types=1);

use App\Controllers\Admin\AdminAuditController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminForexController;
use App\Controllers\Admin\AdminInvestmentController;
use App\Controllers\Admin\AdminKycController;
use App\Controllers\Admin\AdminLedgerController;
use App\Controllers\Admin\AdminRealEstateController;
use App\Controllers\Admin\AdminSettingsController;
use App\Controllers\Admin\AdminUserController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\RoleMiddleware;

$router->group([
    'prefix' => '/admin',
    'middleware' => [AuthMiddleware::class, RoleMiddleware::class . ':super_admin,admin,support,compliance'],
], function ($router): void {
    $router->get('', [AdminDashboardController::class, 'overview']);

    $router->get('/users', [AdminUserController::class, 'index']);
    $router->post('/users/status', [
        'controller' => AdminUserController::class,
        'action' => 'updateStatus',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/users/role', [
        'controller' => AdminUserController::class,
        'action' => 'updateRole',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->get('/kyc', [AdminKycController::class, 'index']);
    $router->post('/kyc/approve', [
        'controller' => AdminKycController::class,
        'action' => 'approve',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/kyc/reject', [
        'controller' => AdminKycController::class,
        'action' => 'reject',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->get('/ledger', [AdminLedgerController::class, 'index']);
    $router->get('/ledger/adjust', [AdminLedgerController::class, 'adjustForm']);
    $router->post('/ledger/adjust', [
        'controller' => AdminLedgerController::class,
        'action' => 'adjust',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->get('/investment/plans', [AdminInvestmentController::class, 'index']);
    $router->post('/investment/plans', [
        'controller' => AdminInvestmentController::class,
        'action' => 'create',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/investment/plans/update', [
        'controller' => AdminInvestmentController::class,
        'action' => 'update',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/investment/plans/toggle', [
        'controller' => AdminInvestmentController::class,
        'action' => 'toggleActive',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->get('/forex/instruments', [AdminForexController::class, 'instruments']);
    $router->post('/forex/instruments', [
        'controller' => AdminForexController::class,
        'action' => 'createInstrument',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/forex/instruments/update', [
        'controller' => AdminForexController::class,
        'action' => 'updateInstrument',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/forex/instruments/toggle', [
        'controller' => AdminForexController::class,
        'action' => 'toggleInstrumentActive',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->get('/forex/strategies', [AdminForexController::class, 'strategies']);
    $router->post('/forex/strategies', [
        'controller' => AdminForexController::class,
        'action' => 'createStrategy',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/forex/strategies/update', [
        'controller' => AdminForexController::class,
        'action' => 'updateStrategy',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/forex/strategies/toggle', [
        'controller' => AdminForexController::class,
        'action' => 'toggleStrategyActive',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->get('/realestate/properties', [AdminRealEstateController::class, 'properties']);
    $router->post('/realestate/properties', [
        'controller' => AdminRealEstateController::class,
        'action' => 'createProperty',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/realestate/properties/update', [
        'controller' => AdminRealEstateController::class,
        'action' => 'updateProperty',
        'middleware' => [CsrfMiddleware::class],
    ]);
    $router->post('/realestate/properties/toggle', [
        'controller' => AdminRealEstateController::class,
        'action' => 'togglePropertyActive',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->get('/realestate/listings', [AdminRealEstateController::class, 'listings']);
    $router->post('/realestate/listings/review', [
        'controller' => AdminRealEstateController::class,
        'action' => 'reviewListing',
        'middleware' => [CsrfMiddleware::class],
    ]);

    $router->get('/audit', [AdminAuditController::class, 'index']);

    $router->get('/settings', [AdminSettingsController::class, 'index']);
    $router->post('/settings', [
        'controller' => AdminSettingsController::class,
        'action' => 'update',
        'middleware' => [CsrfMiddleware::class],
    ]);
});
