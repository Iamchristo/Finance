<?php

declare(strict_types=1);

namespace App\Services\Investment;

use App\Enums\WalletSection;
use App\Repositories\InvestmentRepository;
use App\Services\WalletService;
use DateTimeImmutable;

final class AccrualService
{
    public function __construct(
        private readonly InvestmentRepository $investments,
        private readonly WalletService $wallets,
    ) {
    }

    /** @return int number of accruals applied */
    public function runDailyAccrual(): int
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $applied = 0;

        foreach ($this->investments->activeSubscriptionsForAccrual() as $subscription) {
            $dailyRate = $this->dailyRate((string) $subscription['roi_percent'], $subscription['roi_period']);
            $amount = bcmul($subscription['principal_amount'], bcdiv($dailyRate, '100', 10), 8);

            if (bccomp($amount, '0', 8) <= 0) {
                continue;
            }

            $ledgerEntryId = $this->wallets->credit(
                (int) $subscription['user_id'],
                WalletSection::Investment,
                $amount,
                'investment_roi_accrual',
                'investment_subscription',
                (int) $subscription['id'],
                'Daily ROI accrual'
            );

            $recorded = $this->investments->recordAccrual((int) $subscription['id'], $ledgerEntryId, $amount, $today);

            if ($recorded) {
                $this->investments->bumpAccruedTotal((int) $subscription['id'], $amount);
                $applied++;
            }
        }

        return $applied;
    }

    private function dailyRate(string $roiPercent, string $period): string
    {
        return match ($period) {
            'daily' => $roiPercent,
            'weekly' => bcdiv($roiPercent, '7', 10),
            'monthly' => bcdiv($roiPercent, '30', 10),
            default => '0',
        };
    }
}
