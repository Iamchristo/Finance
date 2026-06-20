<?php

declare(strict_types=1);

use App\Controllers\Marketing\HomeController;

$router->get('/', [HomeController::class, 'index']);
