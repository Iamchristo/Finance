<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Exceptions\InsufficientBalanceException;
use App\Enums\WalletSection;
use PDO;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * The single choke point for every balance mutation across all three verticals.
 * No vertical service is allowed to write to `wallets` directly — they only ever
 * call credit()/debit()/transfer() here, which keeps locking and ledger-writing
 * consistent in one place instead of duplicated (and inevitably diverging) across
 * Investment/Forex/RealEstate code.
 */
final class WalletService
{
    private const MAX_LOCK_RETRIES = 5;

    public function ensureWalletExists(int $userId): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id FROM wallets WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        if ($stmt->fetchColumn() === false) {
            $insert = $pdo->prepare('INSERT INTO wallets (user_id) VALUES (:user_id)');
            $insert->execute(['user_id' => $userId]);
        }
    }

    /** @return array{main: string, investment: string, forex: string, realestate: string} */
    public function getBalances(int $userId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT main_balance, investment_balance, forex_balance, realestate_balance
             FROM wallets WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return ['main' => '0.00000000', 'investment' => '0.00000000', 'forex' => '0.00000000', 'realestate' => '0.00000000'];
        }

        return [
            'main' => $row['main_balance'],
            'investment' => $row['investment_balance'],
            'forex' => $row['forex_balance'],
            'realestate' => $row['realestate_balance'],
        ];
    }

    public function credit(
        int $userId,
        WalletSection $section,
        string $amount,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $createdByUserId = null,
    ): int {
        return $this->mutate($userId, $section, $amount, 'credit', $type, $referenceType, $referenceId, $description, $createdByUserId);
    }

    public function debit(
        int $userId,
        WalletSection $section,
        string $amount,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $createdByUserId = null,
    ): int {
        return $this->mutate($userId, $section, $amount, 'debit', $type, $referenceType, $referenceId, $description, $createdByUserId);
    }

    /**
     * Move balance between a user's own sub-accounts. Records both legs as
     * linked ledger entries (transfer_out on the source, transfer_in on the
     * destination) so the movement is fully auditable.
     */
    public function transfer(int $userId, WalletSection $from, WalletSection $to, string $amount): void
    {
        if ($from === $to) {
            throw new RuntimeException('Cannot transfer to the same wallet section.');
        }

        Database::transaction(function () use ($userId, $from, $to, $amount): void {
            $outId = $this->debit($userId, $from, $amount, 'transfer_out', 'wallet_transfer', null, "Transfer to {$to->label()}");
            $inId = $this->credit($userId, $to, $amount, 'transfer_in', 'wallet_transfer', null, "Transfer from {$from->label()}");

            $pdo = Database::connection();
            $stmt = $pdo->prepare('UPDATE ledger_entries SET related_transfer_id = :other WHERE id = :self');
            $stmt->execute(['other' => $inId, 'self' => $outId]);
            $stmt->execute(['other' => $outId, 'self' => $inId]);
        });
    }

    private function mutate(
        int $userId,
        WalletSection $section,
        string $amount,
        string $direction,
        string $type,
        ?string $referenceType,
        ?int $referenceId,
        ?string $description,
        ?int $createdByUserId,
    ): int {
        if (!is_numeric($amount) || bccomp($amount, '0', 8) <= 0) {
            throw new RuntimeException('Amount must be a positive numeric value.');
        }

        return Database::transaction(function () use (
            $userId, $section, $amount, $direction, $type, $referenceType, $referenceId, $description, $createdByUserId
        ): int {
            $pdo = Database::connection();
            $column = $section->balanceColumn();

            for ($attempt = 0; $attempt < self::MAX_LOCK_RETRIES; $attempt++) {
                $select = $pdo->prepare("SELECT {$column} AS balance, version FROM wallets WHERE user_id = :user_id FOR UPDATE");
                $select->execute(['user_id' => $userId]);
                $wallet = $select->fetch();

                if ($wallet === false) {
                    throw new RuntimeException("No wallet found for user {$userId}.");
                }

                $currentBalance = $wallet['balance'];
                $version = (int) $wallet['version'];

                if ($direction === 'debit' && bccomp($currentBalance, $amount, 8) < 0) {
                    throw new InsufficientBalanceException($section->value);
                }

                $newBalance = $direction === 'credit'
                    ? bcadd($currentBalance, $amount, 8)
                    : bcsub($currentBalance, $amount, 8);

                $update = $pdo->prepare(
                    "UPDATE wallets SET {$column} = :new_balance, version = version + 1
                     WHERE user_id = :user_id AND version = :version"
                );
                $update->execute([
                    'new_balance' => $newBalance,
                    'user_id' => $userId,
                    'version' => $version,
                ]);

                if ($update->rowCount() === 0) {
                    // Lost the optimistic-lock race to a concurrent mutation; retry.
                    continue;
                }

                $ledgerStmt = $pdo->prepare(
                    'INSERT INTO ledger_entries
                        (uuid, user_id, wallet_section, direction, amount, balance_after, type, reference_type, reference_id, description, created_by_user_id)
                     VALUES (:uuid, :user_id, :section, :direction, :amount, :balance_after, :type, :reference_type, :reference_id, :description, :created_by_user_id)'
                );
                $ledgerStmt->execute([
                    'uuid' => Uuid::uuid4()->toString(),
                    'user_id' => $userId,
                    'section' => $section->value,
                    'direction' => $direction,
                    'amount' => $amount,
                    'balance_after' => $newBalance,
                    'type' => $type,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'description' => $description,
                    'created_by_user_id' => $createdByUserId,
                ]);

                return (int) $pdo->lastInsertId();
            }

            throw new RuntimeException('Could not acquire wallet lock after retries; please try again.');
        });
    }
}
