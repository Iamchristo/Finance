<?php

declare(strict_types=1);

namespace App\Controllers\Marketing;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ForexRepository;
use App\Repositories\InvestmentRepository;
use App\Repositories\RealEstateRepository;

final class MarketingController extends Controller
{
    public function __construct(
        private readonly InvestmentRepository $investments,
        private readonly ForexRepository $forex,
        private readonly RealEstateRepository $realEstate,
    ) {
    }

    public function invest(Request $request): Response
    {
        return $this->view('marketing/invest/index', [
            'title' => 'High-Yield Investment Plans',
            'plans' => $this->investments->activePlans(),
        ]);
    }

    public function investPlan(Request $request): Response
    {
        $plan = $this->investments->findPlanBySlug((string) $request->attribute('slug'));

        if ($plan === null) {
            return Response::html('404 Not Found', 404);
        }

        return $this->view('marketing/invest/show', [
            'title' => $plan['name'],
            'plan' => $plan,
        ]);
    }

    public function trading(Request $request): Response
    {
        $instruments = $this->forex->activeInstruments();
        $byClass = [];
        foreach ($instruments as $instrument) {
            $byClass[$instrument['class_name']][] = $instrument;
        }

        return $this->view('marketing/trading/index', [
            'title' => 'Forex, Crypto, Indices & Commodities Trading',
            'byClass' => $byClass,
            'strategies' => $this->forex->activeStrategies(),
        ]);
    }

    public function tradingInstrument(Request $request): Response
    {
        $instrument = $this->forex->findBySymbol((string) $request->attribute('symbol'));

        if ($instrument === null) {
            return Response::html('404 Not Found', 404);
        }

        return $this->view('marketing/trading/show', [
            'title' => $instrument['display_name'],
            'instrument' => $instrument,
        ]);
    }

    public function tradingStrategies(Request $request): Response
    {
        return $this->view('marketing/trading/strategies', [
            'title' => 'Trading Strategies',
            'strategies' => $this->forex->activeStrategies(),
        ]);
    }

    public function realEstate(Request $request): Response
    {
        return $this->view('marketing/realestate/index', [
            'title' => 'Real Estate Investment & Marketplace',
            'properties' => $this->realEstate->activeProperties(),
        ]);
    }

    public function realEstateProperty(Request $request): Response
    {
        $property = $this->realEstate->findPropertyBySlug((string) $request->attribute('slug'));

        if ($property === null) {
            return Response::html('404 Not Found', 404);
        }

        return $this->view('marketing/realestate/show', [
            'title' => $property['title'],
            'property' => $property,
        ]);
    }
}
