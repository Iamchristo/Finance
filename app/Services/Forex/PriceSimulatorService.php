<?php

declare(strict_types=1);

namespace App\Services\Forex;

use App\Repositories\ForexRepository;
use DateTimeImmutable;

/**
 * Drives the simulated price feed: a mean-reverting random walk per
 * instrument, ticked on a schedule by a console command. There is no real
 * market data source — this platform is an explicitly simulated/demo
 * trading environment, never a live brokerage.
 */
final class PriceSimulatorService
{
    private const REVERSION_STRENGTH = '0.02';

    public function __construct(private readonly ForexRepository $forex)
    {
    }

    /** @return int number of instruments ticked */
    public function tick(): int
    {
        $now = new DateTimeImmutable();
        $ticked = 0;

        foreach ($this->forex->activeInstruments() as $instrument) {
            $newPrice = $this->nextPrice($instrument['current_price'], $instrument['previous_close'], $instrument['volatility_factor']);
            $newPrice = bcadd($newPrice, '0', (int) $instrument['price_precision']);

            if (bccomp($newPrice, '0') <= 0) {
                continue;
            }

            $changePercent = bccomp($instrument['previous_close'], '0') > 0
                ? bcmul(bcdiv(bcsub($newPrice, $instrument['previous_close'], 10), $instrument['previous_close'], 10), '100', 4)
                : '0.0000';

            $this->forex->updatePrice((int) $instrument['id'], $newPrice, $instrument['previous_close'], $changePercent);

            foreach (['1m', '5m', '15m', '1h', '4h', '1d'] as $timeframe) {
                $bucketStart = $this->bucketStart($now, $timeframe);
                $this->forex->upsertCandle((int) $instrument['id'], $timeframe, $bucketStart, $newPrice);
            }

            $ticked++;
        }

        return $ticked;
    }

    private function nextPrice(string $currentPrice, string $previousClose, string $volatilityFactor): string
    {
        $randomShock = bcmul((string) ((mt_rand(-1000, 1000) / 1000)), $volatilityFactor, 10);
        $randomShock = bcmul($currentPrice, $randomShock, 10);

        $deviation = bcsub($currentPrice, $previousClose, 10);
        $reversion = bcmul($deviation, '-' . self::REVERSION_STRENGTH, 10);

        return bcadd(bcadd($currentPrice, $randomShock, 10), $reversion, 10);
    }

    private function bucketStart(DateTimeImmutable $now, string $timeframe): string
    {
        $minutes = match ($timeframe) {
            '1m' => 1,
            '5m' => 5,
            '15m' => 15,
            '1h' => 60,
            '4h' => 240,
            '1d' => 1440,
        };

        $epochMinutes = (int) floor($now->getTimestamp() / 60);
        $flooredMinutes = $epochMinutes - ($epochMinutes % $minutes);
        $bucketTimestamp = $flooredMinutes * 60;

        return (new DateTimeImmutable('@' . $bucketTimestamp))->format('Y-m-d H:i:s');
    }
}
