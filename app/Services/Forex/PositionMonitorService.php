<?php

declare(strict_types=1);

namespace App\Services\Forex;

use App\Repositories\ForexRepository;

/**
 * Evaluates every open simulated position after each price tick and triggers
 * stop-loss, take-profit, or margin-call liquidation via OrderService::settleClose().
 * Liquidation takes priority over SL/TP since a margin call means the account
 * can no longer absorb further adverse movement.
 */
final class PositionMonitorService
{
    public function __construct(
        private readonly ForexRepository $forex,
        private readonly OrderService $orders,
    ) {
    }

    /** @return int number of positions closed */
    public function evaluateAll(): int
    {
        $closed = 0;

        foreach ($this->forex->allOpenPositions() as $position) {
            $currentPrice = $position['current_price'];

            $pnl = $position['side'] === 'long'
                ? bcmul(bcsub($currentPrice, $position['entry_price'], 8), $position['quantity'], 8)
                : bcmul(bcsub($position['entry_price'], $currentPrice, 8), $position['quantity'], 8);

            $equity = bcadd($position['margin_used'], $pnl, 8);

            if (bccomp($equity, '0', 8) <= 0) {
                $this->orders->settleClose($position, $currentPrice, 'liquidated');
                $closed++;
                continue;
            }

            if ($this->hitStopLoss($position, $currentPrice)) {
                $this->orders->settleClose($position, $position['stop_loss'], 'closed');
                $closed++;
                continue;
            }

            if ($this->hitTakeProfit($position, $currentPrice)) {
                $this->orders->settleClose($position, $position['take_profit'], 'closed');
                $closed++;
            }
        }

        return $closed;
    }

    /** @param array<string, mixed> $position */
    private function hitStopLoss(array $position, string $currentPrice): bool
    {
        if ($position['stop_loss'] === null) {
            return false;
        }

        return $position['side'] === 'long'
            ? bccomp($currentPrice, $position['stop_loss'], 8) <= 0
            : bccomp($currentPrice, $position['stop_loss'], 8) >= 0;
    }

    /** @param array<string, mixed> $position */
    private function hitTakeProfit(array $position, string $currentPrice): bool
    {
        if ($position['take_profit'] === null) {
            return false;
        }

        return $position['side'] === 'long'
            ? bccomp($currentPrice, $position['take_profit'], 8) >= 0
            : bccomp($currentPrice, $position['take_profit'], 8) <= 0;
    }
}
