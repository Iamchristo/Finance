<?php

declare(strict_types=1);

use App\Controllers\Marketing\ContentController;
use App\Controllers\Marketing\HomeController;
use App\Controllers\Marketing\MarketingController;

$router->get('/', [HomeController::class, 'index']);

$router->get('/invest', [MarketingController::class, 'invest']);
$router->get('/invest/{slug}', [MarketingController::class, 'investPlan']);

$router->get('/trading', [MarketingController::class, 'trading']);
$router->get('/trading/strategies', [MarketingController::class, 'tradingStrategies']);
$router->get('/trading/{symbol}', [MarketingController::class, 'tradingInstrument']);

$router->get('/real-estate', [MarketingController::class, 'realEstate']);
$router->get('/real-estate/{slug}', [MarketingController::class, 'realEstateProperty']);

$router->get('/faq', [ContentController::class, 'faq']);
$router->get('/calculators', [ContentController::class, 'calculators']);

$router->get('/blog', [ContentController::class, 'blogIndex']);
$router->get('/blog/{slug}', [ContentController::class, 'blogShow']);

$router->get('/legal/terms', [ContentController::class, 'legalTerms']);
$router->get('/legal/privacy', [ContentController::class, 'legalPrivacy']);
$router->get('/legal/risk-disclosure', [ContentController::class, 'legalRiskDisclosure']);
$router->get('/legal/simulated-disclosure', [ContentController::class, 'legalSimulatedDisclosure']);
