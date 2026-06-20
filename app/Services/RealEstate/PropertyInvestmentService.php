<?php

declare(strict_types=1);

namespace App\Services\RealEstate;

use App\Core\Database;
use App\Core\Exceptions\ValidationException;
use App\Enums\WalletSection;
use App\Repositories\RealEstateRepository;
use App\Services\WalletService;

final class PropertyInvestmentService
{
    public function __construct(
        private readonly RealEstateRepository $realEstate,
        private readonly WalletService $wallets,
    ) {
    }

    public function invest(int $userId, int $propertyId, int $shares): int
    {
        if ($shares < 1) {
            throw new ValidationException(['shares' => 'You must purchase at least one share.']);
        }

        return Database::transaction(function () use ($userId, $propertyId, $shares): int {
            $property = $this->realEstate->lockProperty($propertyId);

            if ($property === null || (int) $property['is_active'] !== 1 || $property['mode'] !== 'fractional') {
                throw new ValidationException(['property' => 'This property is not open for fractional investment.']);
            }

            if ($property['funding_status'] !== 'open') {
                throw new ValidationException(['property' => 'This property is no longer accepting investment.']);
            }

            $remainingShares = (int) $property['total_shares'] - (int) $property['shares_sold'];

            if ($shares > $remainingShares) {
                throw new ValidationException(['shares' => "Only {$remainingShares} shares remain available."]);
            }

            $amount = bcmul((string) $shares, $property['share_price'], 8);

            $this->wallets->debit(
                $userId,
                WalletSection::RealEstate,
                $amount,
                'realestate_investment',
                're_property',
                $propertyId,
                "Invested in {$property['title']}"
            );

            $this->realEstate->recordShares($propertyId, $shares);

            return $this->realEstate->createInvestment($userId, $propertyId, $shares, $amount);
        });
    }
}
