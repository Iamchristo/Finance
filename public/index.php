<?php

declare(strict_types=1);

use App\Core\Application;

require dirname(__DIR__) . '/bootstrap.php';

$app = new Application();
$app->loadRoutes(dirname(__DIR__) . '/routes');
$app->run();
