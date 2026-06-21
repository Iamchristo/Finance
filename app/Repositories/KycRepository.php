<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class KycRepository
{
    /** @return array<int, array<string, mixed>> */
    public function pending(): array
    {
        $stmt = Database::connection()->query(
            "SELECT k.*, u.first_name, u.last_name, u.email FROM kyc_submissions k
             JOIN users u ON u.id = k.user_id
             WHERE k.status = 'pending' ORDER BY k.submitted_at"
        );

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT k.*, u.first_name, u.last_name, u.email FROM kyc_submissions k
             JOIN users u ON u.id = k.user_id
             ORDER BY k.submitted_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM kyc_submissions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function review(int $id, string $status, int $reviewerUserId, ?string $rejectionReason = null): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE kyc_submissions
             SET status = :status, reviewed_by_user_id = :reviewer, rejection_reason = :reason, reviewed_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'reviewer' => $reviewerUserId, 'reason' => $rejectionReason, 'id' => $id]);
    }
}
