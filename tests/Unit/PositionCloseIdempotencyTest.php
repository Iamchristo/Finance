<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\WalletSection;
use App\Repositories\ForexRepository;
use App\Services\Forex\OrderService;
use App\Services\WalletService;
use Tests\Support\DatabaseTestCase;

/**
 * An overlapping position-monitor cron run (or a duplicate manual close
 * request) must not pay out the same position's margin+PnL twice. Both
 * settleClose() calls below race over the same already-fetched position
 * snapshot, mirroring what two concurrent evaluateAll() passes would see.
 */
final class PositionCloseIdempotencyTest extends DatabaseTestCase
{
    public function testSettleCloseOnAnAlreadyClosedPositionIsANoOp(): void
    {
        $userId = $this->createTestUser();
        $forex = new ForexRepository();
        $wallet = new WalletService();
        $orders = new OrderService($forex, $wallet);

        $instrument = $this->firstActiveInstrument($forex);
        $wallet->credit($userId, WalletSection::Forex, '10000.00000000', 'admin_adjustment_credit');

        $opened = $orders->placeMarketOrder(
            $userId,
            (int) $instrument['id'],
            'buy',
            $instrument['min_trade_size'],
            1
        );

        $position = $forex->findPosition($opened['position_id']);
        self::assertNotNull($position);

        $firstClose = $orders->settleClose($position, $instrument['current_price'], 'closed');
        self::assertTrue($firstClose, 'The first close of an open position must succeed.');

        $balanceAfterFirstClose = $wallet->getBalances($userId)['forex'];

        $secondClose = $orders->settleClose($position, $instrument['current_price'], 'closed');
        self::assertFalse($secondClose, 'A second close of an already-closed position must be a no-op.');

        $balanceAfterSecondClose = $wallet->getBalances($userId)['forex'];
        self::assertSame(
            $balanceAfterFirstClose,
            $balanceAfterSecondClose,
            'Balance must be unchanged by the duplicate close.'
        );

        $closedPosition = $forex->findPosition($opened['position_id']);
        self::assertSame('closed', $closedPosition['status']);
    }

    /** @return array<string, mixed> */
    private function firstActiveInstrument(ForexRepository $forex): array
    {
        $instruments = $forex->activeInstruments();
        self::assertNotEmpty($instruments, 'Fixture data must include at least one active instrument.');

        return $instruments[0];
    }
}
