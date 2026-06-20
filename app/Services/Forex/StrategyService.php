<?php

declare(strict_types=1);

namespace App\Services\Forex;

use App\Core\Database;
use App\Core\Exceptions\ValidationException;
use App\Enums\WalletSection;
use App\Repositories\ForexRepository;
use App\Services\WalletService;

final class StrategyService
{
    public function __construct(
        private readonly ForexRepository $forex,
        private readonly WalletService $wallets,
    ) {
    }

    public function subscribe(int $userId, int $strategyId, string $amount): int
    {
        $strategy = $this->forex->findStrategy($strategyId);

        if ($strategy === null || (int) $strategy['is_active'] !== 1) {
            throw new ValidationException(['strategy' => 'This strategy is not available.']);
        }

        if (bccomp($amount, '0') <= 0) {
            throw new ValidationException(['amount' => 'Allocation amount must be greater than zero.']);
        }

        return Database::transaction(function () use ($userId, $strategy, $amount): int {
            $this->wallets->debit(
                $userId,
                WalletSection::Forex,
                $amount,
                'forex_fee',
                'fx_strategy',
                (int) $strategy['id'],
                "Allocated to strategy: {$strategy['name']}"
            );

            return $this->forex->createStrategySubscription($userId, (int) $strategy['id'], $amount);
        });
    }
}
