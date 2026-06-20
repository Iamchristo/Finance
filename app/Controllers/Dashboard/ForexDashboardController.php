<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use App\Core\Controller;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\ForexRepository;
use App\Services\Forex\OrderService;
use App\Services\Forex\StrategyService;
use App\Services\WalletService;

final class ForexDashboardController extends Controller
{
    public function __construct(
        private readonly ForexRepository $forex,
        private readonly OrderService $orders,
        private readonly StrategyService $strategies,
        private readonly WalletService $wallets,
    ) {
    }

    public function overview(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('dashboard/forex/overview', [
            'title' => 'Trading Overview',
            'balances' => $this->wallets->getBalances($userId),
            'positions' => $this->forex->openPositionsForUser($userId),
            'instruments' => $this->forex->activeInstruments(),
        ]);
    }

    public function markets(Request $request): Response
    {
        return $this->view('dashboard/forex/markets', [
            'title' => 'Markets',
            'instruments' => $this->forex->activeInstruments(),
        ]);
    }

    public function trade(Request $request): Response
    {
        $symbol = (string) $request->attribute('symbol');
        $instrument = $this->forex->findBySymbol($symbol);

        if ($instrument === null) {
            return $this->redirect('/dashboard/forex/markets');
        }

        return $this->view('dashboard/forex/trade', [
            'title' => $instrument['symbol'] . ' Trade',
            'instrument' => $instrument,
            'candles' => $this->forex->candles((int) $instrument['id'], '1h', 200),
        ]);
    }

    public function placeOrder(Request $request): Response
    {
        $userId = $this->currentUserId();
        $instrumentId = (int) $request->input('instrument_id', 0);
        $side = (string) $request->input('side', 'buy');
        $quantity = (string) $request->input('quantity', '0');
        $leverage = (int) $request->input('leverage', 1);
        $stopLoss = $request->input('stop_loss') !== '' ? (string) $request->input('stop_loss') : null;
        $takeProfit = $request->input('take_profit') !== '' ? (string) $request->input('take_profit') : null;

        try {
            $this->orders->placeMarketOrder($userId, $instrumentId, $side, $quantity, $leverage, $stopLoss, $takeProfit);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->back($request, '/dashboard/forex/markets');
        }

        Session::flash('success', 'Order filled and position opened.');

        return $this->redirect('/dashboard/forex/positions');
    }

    public function positions(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('dashboard/forex/positions', [
            'title' => 'Open Positions',
            'positions' => $this->forex->openPositionsForUser($userId),
        ]);
    }

    public function closePosition(Request $request): Response
    {
        $userId = $this->currentUserId();
        $positionId = (int) $request->input('position_id', 0);

        try {
            $this->orders->closePosition($userId, $positionId);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/dashboard/forex/positions');
        }

        Session::flash('success', 'Position closed.');

        return $this->redirect('/dashboard/forex/positions');
    }

    public function orderHistory(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('dashboard/forex/orders', [
            'title' => 'Order History',
            'orders' => $this->forex->orderHistory($userId),
        ]);
    }

    public function watchlist(Request $request): Response
    {
        $userId = $this->currentUserId();
        $watchlistId = $this->forex->defaultWatchlistId($userId);
        $watched = $this->forex->watchlistItems($watchlistId);
        $watchedIds = array_map(static fn (array $row): int => (int) $row['id'], $watched);

        return $this->view('dashboard/forex/watchlist', [
            'title' => 'Watchlist',
            'watched' => $watched,
            'instruments' => array_filter(
                $this->forex->activeInstruments(),
                static fn (array $row): bool => !in_array((int) $row['id'], $watchedIds, true)
            ),
        ]);
    }

    public function addToWatchlist(Request $request): Response
    {
        $userId = $this->currentUserId();
        $watchlistId = $this->forex->defaultWatchlistId($userId);
        $this->forex->addToWatchlist($watchlistId, (int) $request->input('instrument_id', 0));

        return $this->redirect('/dashboard/forex/watchlist');
    }

    public function removeFromWatchlist(Request $request): Response
    {
        $userId = $this->currentUserId();
        $watchlistId = $this->forex->defaultWatchlistId($userId);
        $this->forex->removeFromWatchlist($watchlistId, (int) $request->input('instrument_id', 0));

        return $this->redirect('/dashboard/forex/watchlist');
    }

    public function strategies(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('dashboard/forex/strategies', [
            'title' => 'Strategies',
            'strategies' => $this->forex->activeStrategies(),
            'subscriptions' => $this->forex->strategySubscriptionsForUser($userId),
        ]);
    }

    public function subscribeStrategy(Request $request): Response
    {
        $userId = $this->currentUserId();
        $strategyId = (int) $request->input('strategy_id', 0);
        $amount = (string) $request->input('amount', '0');

        try {
            $this->strategies->subscribe($userId, $strategyId, $amount);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/dashboard/forex/strategies');
        }

        Session::flash('success', 'Allocated to strategy successfully.');

        return $this->redirect('/dashboard/forex/strategies');
    }
}
