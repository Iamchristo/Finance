<?php

declare(strict_types=1);

namespace App\Services\Investment;

use App\Core\Database;
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

    /**
     * Re-running this for a day that was already accrued must not double-credit.
     * Each subscription's check-credit-record sequence runs inside one transaction,
     * with the lock held by hasAccrualForDate() (a FOR UPDATE read) for its whole
     * duration, so a duplicate run sees the prior accrual and skips the credit
     * entirely rather than crediting first and discovering the duplicate after.
     *
     * @return int number of accruals applied
     */
    public function runDailyAccrual(): int
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $applied = 0;

        foreach ($this->investments->activeSubscriptionsForAccrual() as $subscription) {
            $subscriptionId = (int) $subscription['id'];
            $dailyRate = $this->dailyRate((string) $subscription['roi_percent'], $subscription['roi_period']);
            $amount = bcmul($subscription['principal_amount'], bcdiv($dailyRate, '100', 10), 8);

            if (bccomp($amount, '0', 8) <= 0) {
                continue;
            }

            $wasApplied = Database::transaction(function () use ($subscription, $subscriptionId, $amount, $today): bool {
                if ($this->investments->hasAccrualForDate($subscriptionId, $today)) {
                    return false;
                }

                $ledgerEntryId = $this->wallets->credit(
                    (int) $subscription['user_id'],
                    WalletSection::Investment,
                    $amount,
                    'investment_roi_accrual',
                    'investment_subscription',
                    $subscriptionId,
                    'Daily ROI accrual'
                );

                $this->investments->recordAccrual($subscriptionId, $ledgerEntryId, $amount, $today);
                $this->investments->bumpAccruedTotal($subscriptionId, $amount);

                return true;
            });

            if ($wasApplied) {
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
