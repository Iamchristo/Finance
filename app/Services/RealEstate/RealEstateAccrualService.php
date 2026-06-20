<?php

declare(strict_types=1);

namespace App\Services\RealEstate;

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

    /** @return int number of accruals applied */
    public function runDailyAccrual(): int
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $applied = 0;

        foreach ($this->realEstate->activeInvestmentsForAccrual() as $investment) {
            $dailyRate = bcdiv($investment['expected_annual_roi_percent'], '365', 10);
            $amount = bcmul($investment['amount_invested'], bcdiv($dailyRate, '100', 10), 8);

            if (bccomp($amount, '0') <= 0) {
                continue;
            }

            $ledgerEntryId = $this->wallets->credit(
                (int) $investment['user_id'],
                WalletSection::RealEstate,
                $amount,
                'realestate_roi_accrual',
                're_property_investment',
                (int) $investment['id'],
                'Daily rental/ROI accrual'
            );

            $recorded = $this->realEstate->recordAccrual((int) $investment['id'], $ledgerEntryId, $amount, $today);

            if ($recorded) {
                $applied++;
            }
        }

        return $applied;
    }
}
