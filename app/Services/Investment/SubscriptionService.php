<?php

declare(strict_types=1);

namespace App\Services\Investment;

use App\Core\Database;
use App\Core\Exceptions\ValidationException;
use App\Enums\WalletSection;
use App\Repositories\InvestmentRepository;
use App\Services\WalletService;

final class SubscriptionService
{
    public function __construct(
        private readonly InvestmentRepository $investments,
        private readonly WalletService $wallets,
    ) {
    }

    public function subscribe(int $userId, int $planId, string $amount): int
    {
        $plan = $this->investments->findPlanById($planId);

        if ($plan === null) {
            throw new ValidationException(['plan' => 'This plan is not available.']);
        }

        if (bccomp($amount, $plan['min_amount']) < 0) {
            throw new ValidationException(['amount' => "Minimum investment for this plan is {$plan['min_amount']}."]);
        }

        if ($plan['max_amount'] !== null && bccomp($amount, $plan['max_amount']) > 0) {
            throw new ValidationException(['amount' => "Maximum investment for this plan is {$plan['max_amount']}."]);
        }

        return Database::transaction(function () use ($userId, $plan, $amount): int {
            $this->wallets->debit(
                $userId,
                WalletSection::Investment,
                $amount,
                'investment_subscribe',
                'investment_plan',
                (int) $plan['id'],
                "Subscribed to {$plan['name']}"
            );

            return $this->investments->createSubscription($userId, (int) $plan['id'], $amount, (int) $plan['duration_days']);
        });
    }
}
