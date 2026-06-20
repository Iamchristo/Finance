<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class WalletRepository
{
    /** @return array<int, array<string, mixed>> */
    public function ledgerForUser(int $userId, int $limit = 100): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM ledger_entries WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT :limit'
        );
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
