<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AuditRepository
{
    /** @return array<int, array<string, mixed>> */
    public function paginate(int $page, int $perPage): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = Database::connection()->prepare(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS actor_name FROM audit_log a
             LEFT JOIN users u ON u.id = a.actor_user_id
             ORDER BY a.id DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
    }
}
