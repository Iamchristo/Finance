<?php

declare(strict_types=1);

namespace App\Enums;

enum WalletSection: string
{
    case Main = 'main';
    case Investment = 'investment';
    case Forex = 'forex';
    case RealEstate = 'realestate';

    public function balanceColumn(): string
    {
        return match ($this) {
            self::Main => 'main_balance',
            self::Investment => 'investment_balance',
            self::Forex => 'forex_balance',
            self::RealEstate => 'realestate_balance',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Main => 'Main',
            self::Investment => 'Investment',
            self::Forex => 'Forex & Trading',
            self::RealEstate => 'Real Estate',
        };
    }
}
