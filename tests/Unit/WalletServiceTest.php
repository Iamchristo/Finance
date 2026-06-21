<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Database;
use App\Core\Exceptions\InsufficientBalanceException;
use App\Enums\WalletSection;
use App\Services\WalletService;
use RuntimeException;
use Tests\Support\DatabaseTestCase;

final class WalletServiceTest extends DatabaseTestCase
{
    private WalletService $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = new WalletService();
    }

    public function testCreditIncreasesBalanceAndWritesLedgerEntry(): void
    {
        $userId = $this->createTestUser();

        $ledgerId = $this->wallet->credit($userId, WalletSection::Main, '100.00000000', 'admin_adjustment_credit');

        $balances = $this->wallet->getBalances($userId);
        self::assertSame('100.00000000', $balances['main']);

        $entry = $this->fetchLedgerEntry($ledgerId);
        self::assertSame('credit', $entry['direction']);
        self::assertSame('100.00000000', $entry['amount']);
        self::assertSame('100.00000000', $entry['balance_after']);
    }

    public function testDebitDecreasesBalanceAndWritesLedgerEntry(): void
    {
        $userId = $this->createTestUser();
        $this->wallet->credit($userId, WalletSection::Main, '100.00000000', 'admin_adjustment_credit');

        $ledgerId = $this->wallet->debit($userId, WalletSection::Main, '40.00000000', 'admin_adjustment_debit');

        $balances = $this->wallet->getBalances($userId);
        self::assertSame('60.00000000', $balances['main']);

        $entry = $this->fetchLedgerEntry($ledgerId);
        self::assertSame('debit', $entry['direction']);
        self::assertSame('60.00000000', $entry['balance_after']);
    }

    public function testDebitBeyondBalanceThrowsInsufficientBalanceException(): void
    {
        $userId = $this->createTestUser();
        $this->wallet->credit($userId, WalletSection::Main, '10.00000000', 'admin_adjustment_credit');

        $this->expectException(InsufficientBalanceException::class);

        $this->wallet->debit($userId, WalletSection::Main, '10.00000001', 'admin_adjustment_debit');
    }

    public function testTransferMovesBalanceBetweenSectionsAndLinksLedgerEntries(): void
    {
        $userId = $this->createTestUser();
        $this->wallet->credit($userId, WalletSection::Main, '100.00000000', 'admin_adjustment_credit');

        $this->wallet->transfer($userId, WalletSection::Main, WalletSection::Investment, '30.00000000');

        $balances = $this->wallet->getBalances($userId);
        self::assertSame('70.00000000', $balances['main']);
        self::assertSame('30.00000000', $balances['investment']);

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT id, related_transfer_id, direction FROM ledger_entries WHERE user_id = :user_id AND type IN (\'transfer_out\', \'transfer_in\')'
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        self::assertCount(2, $rows);
        $byDirection = [];
        foreach ($rows as $row) {
            $byDirection[$row['direction']] = $row;
        }

        self::assertSame((int) $byDirection['credit']['id'], (int) $byDirection['debit']['related_transfer_id']);
        self::assertSame((int) $byDirection['debit']['id'], (int) $byDirection['credit']['related_transfer_id']);
    }

    public function testTransferToSameSectionThrowsRuntimeException(): void
    {
        $userId = $this->createTestUser();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot transfer to the same wallet section.');

        $this->wallet->transfer($userId, WalletSection::Main, WalletSection::Main, '10.00000000');
    }

    public function testCreditWithZeroAmountThrowsRuntimeException(): void
    {
        $userId = $this->createTestUser();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Amount must be a positive numeric value.');

        $this->wallet->credit($userId, WalletSection::Main, '0', 'deposit');
    }

    public function testCreditWithNegativeAmountThrowsRuntimeException(): void
    {
        $userId = $this->createTestUser();

        $this->expectException(RuntimeException::class);

        $this->wallet->credit($userId, WalletSection::Main, '-5.00000000', 'deposit');
    }

    public function testCreditWithNonNumericAmountThrowsRuntimeException(): void
    {
        $userId = $this->createTestUser();

        $this->expectException(RuntimeException::class);

        $this->wallet->credit($userId, WalletSection::Main, 'not-a-number', 'deposit');
    }

    /** @return array<string, mixed> */
    private function fetchLedgerEntry(int $id): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM ledger_entries WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        self::assertIsArray($row);

        return $row;
    }
}
