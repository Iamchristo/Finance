<?php

declare(strict_types=1);

namespace App\Services\Forex;

use App\Core\Database;
use App\Core\Exceptions\ValidationException;
use App\Enums\WalletSection;
use App\Repositories\ForexRepository;
use App\Services\WalletService;
use RuntimeException;

final class OrderService
{
    public function __construct(
        private readonly ForexRepository $forex,
        private readonly WalletService $wallets,
    ) {
    }

    /** @return array{order_id: int, position_id: int} */
    public function placeMarketOrder(
        int $userId,
        int $instrumentId,
        string $side,
        string $quantity,
        int $leverage,
        ?string $stopLoss = null,
        ?string $takeProfit = null,
    ): array {
        $instrument = $this->forex->findById($instrumentId);

        if ($instrument === null || (int) $instrument['is_active'] !== 1) {
            throw new ValidationException(['instrument' => 'This instrument is not available for trading.']);
        }

        if (!in_array($side, ['buy', 'sell'], true)) {
            throw new ValidationException(['side' => 'Side must be buy or sell.']);
        }

        if (bccomp($quantity, $instrument['min_trade_size'], 8) < 0) {
            throw new ValidationException(['quantity' => "Minimum trade size is {$instrument['min_trade_size']}."]);
        }

        if ($leverage < 1 || $leverage > (int) $instrument['leverage_max']) {
            throw new ValidationException(['leverage' => "Leverage must be between 1 and {$instrument['leverage_max']}x."]);
        }

        $entryPrice = $instrument['current_price'];
        $notional = bcmul($quantity, $entryPrice, 8);
        $margin = bcdiv($notional, (string) $leverage, 8);

        return Database::transaction(function () use (
            $userId, $instrumentId, $side, $quantity, $leverage, $stopLoss, $takeProfit, $entryPrice, $margin
        ): array {
            $this->wallets->debit(
                $userId,
                WalletSection::Forex,
                $margin,
                'forex_order_open',
                'fx_instrument',
                $instrumentId,
                'Margin reserved for new position'
            );

            $orderId = $this->forex->createOrder($userId, $instrumentId, $side, 'market', $quantity, $leverage);
            $this->forex->markOrderFilled($orderId, $entryPrice);

            $positionSide = $side === 'buy' ? 'long' : 'short';
            $positionId = $this->forex->openPosition(
                $userId,
                $instrumentId,
                $orderId,
                $positionSide,
                $quantity,
                $entryPrice,
                $leverage,
                $margin,
                $stopLoss,
                $takeProfit
            );

            return ['order_id' => $orderId, 'position_id' => $positionId];
        });
    }

    public function closePosition(int $userId, int $positionId): void
    {
        $position = $this->forex->findPosition($positionId);

        if ($position === null || (int) $position['user_id'] !== $userId) {
            throw new ValidationException(['position' => 'Position not found.']);
        }

        if ($position['status'] !== 'open') {
            throw new ValidationException(['position' => 'Position is already closed.']);
        }

        $instrument = $this->forex->findById((int) $position['instrument_id']);

        if ($instrument === null) {
            throw new RuntimeException('Instrument no longer exists.');
        }

        $this->settleClose($position, $instrument['current_price'], 'closed');
    }

    /**
     * Closes a position at a given price with a given terminal status.
     * Shared by manual closes (status=closed) and the position-monitor cron
     * (status=closed for SL/TP hits, status=liquidated for margin calls).
     *
     * closePosition() is gated on status='open', so if a concurrent caller
     * (an overlapping cron run, or a duplicate manual close) already closed
     * this position, it returns false here and the wallet credit is skipped
     * — the position can only ever be paid out once.
     *
     * @param array<string, mixed> $position
     * @return bool whether this call actually closed the position
     */
    public function settleClose(array $position, string $exitPrice, string $status): bool
    {
        $entryPrice = $position['entry_price'];
        $quantity = $position['quantity'];
        $marginUsed = $position['margin_used'];

        $rawPnl = $position['side'] === 'long'
            ? bcmul(bcsub($exitPrice, $entryPrice, 8), $quantity, 8)
            : bcmul(bcsub($entryPrice, $exitPrice, 8), $quantity, 8);

        $maxLoss = bcmul($marginUsed, '-1', 8);
        $pnl = bccomp($rawPnl, $maxLoss, 8) < 0 ? $maxLoss : $rawPnl;

        return Database::transaction(function () use ($position, $exitPrice, $pnl, $marginUsed, $status): bool {
            $didClose = $this->forex->closePosition((int) $position['id'], $exitPrice, $pnl, $status);

            if (!$didClose) {
                return false;
            }

            $returnAmount = bcadd($marginUsed, $pnl, 8);

            if (bccomp($returnAmount, '0', 8) > 0) {
                $this->wallets->credit(
                    (int) $position['user_id'],
                    WalletSection::Forex,
                    $returnAmount,
                    'forex_order_close',
                    'fx_position',
                    (int) $position['id'],
                    $status === 'liquidated' ? 'Position liquidated' : 'Position closed'
                );
            }

            return true;
        });
    }
}
