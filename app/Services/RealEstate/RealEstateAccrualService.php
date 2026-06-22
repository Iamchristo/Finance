<?php

declare(strict_types=1);

namespace App\Services\RealEstate;

use App\Core\Database;
use App\Enums\WalletSection;
use App\Repositories\RealEstateRepository;
use App\Services\WalletService;
use DateTimeImmutable;

final class RealEstateAccrualService
{
    public function __construct(
        private readonly RealEstateRepository $realEstate,
        private readonly WalletService $wallets,
    ) {
    }

    /**
     * Re-running this for a day that was already accrued must not double-credit.
     * Each investment's check-credit-record sequence runs inside one transaction,
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

        foreach ($this->realEstate->activeInvestmentsForAccrual() as $investment) {
            $investmentId = (int) $investment['id'];
            $dailyRate = bcdiv($investment['expected_annual_roi_percent'], '365', 10);
            $amount = bcmul($investment['amount_invested'], bcdiv($dailyRate, '100', 10), 8);

            if (bccomp($amount, '0', 8) <= 0) {
                continue;
            }

            $wasApplied = Database::transaction(function () use ($investment, $investmentId, $amount, $today): bool {
                if ($this->realEstate->hasAccrualForDate($investmentId, $today)) {
                    return false;
                }

                $ledgerEntryId = $this->wallets->credit(
                    (int) $investment['user_id'],
                    WalletSection::RealEstate,
                    $amount,
                    'realestate_roi_accrual',
                    're_property_investment',
                    $investmentId,
                    'Daily rental/ROI accrual'
                );

                $this->realEstate->recordAccrual($investmentId, $ledgerEntryId, $amount, $today);

                return true;
            });

            if ($wasApplied) {
                $applied++;
            }
        }

        return $applied;
    }
}
