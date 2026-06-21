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

    /** @return array<int, array<string, mixed>> paginated ledger entries across all users, for admin explorer */
    public function paginateLedger(int $page, int $perPage, ?string $section = null): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $sql = 'SELECT l.*, u.email, u.first_name, u.last_name FROM ledger_entries l JOIN users u ON u.id = l.user_id';
        $params = [];

        if ($section !== null && $section !== '') {
            $sql .= ' WHERE l.wallet_section = :section';
            $params['section'] = $section;
        }

        $sql .= ' ORDER BY l.id DESC LIMIT :limit OFFSET :offset';

        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countLedger(?string $section = null): int
    {
        $sql = 'SELECT COUNT(*) FROM ledger_entries';
        $params = [];

        if ($section !== null && $section !== '') {
            $sql .= ' WHERE wallet_section = :section';
            $params['section'] = $section;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}
