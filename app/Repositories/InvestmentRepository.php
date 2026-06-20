<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class InvestmentRepository
{
    /** @return array<int, array<string, mixed>> */
    public function activePlans(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM investment_plans WHERE is_active = 1 ORDER BY sort_order, min_amount'
        );

        return $stmt->fetchAll();
    }

    public function findPlanBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM investment_plans WHERE slug = :slug AND is_active = 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findPlanById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM investment_plans WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int, array<string, mixed>> */
    public function subscriptionsForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT s.*, p.name AS plan_name, p.roi_percent, p.roi_period
             FROM investment_subscriptions s
             JOIN investment_plans p ON p.id = s.plan_id
             WHERE s.user_id = :user_id
             ORDER BY s.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function createSubscription(int $userId, int $planId, string $amount, int $durationDays): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO investment_subscriptions (user_id, plan_id, principal_amount, starts_at, ends_at)
             VALUES (:user_id, :plan_id, :amount, NOW(), DATE_ADD(NOW(), INTERVAL :days DAY))'
        );
        $stmt->execute(['user_id' => $userId, 'plan_id' => $planId, 'amount' => $amount, 'days' => $durationDays]);

        return (int) $pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> active subscriptions due for accrual today */
    public function activeSubscriptionsForAccrual(): array
    {
        $stmt = Database::connection()->query(
            "SELECT s.*, p.roi_percent, p.roi_period
             FROM investment_subscriptions s
             JOIN investment_plans p ON p.id = s.plan_id
             WHERE s.status = 'active' AND (s.ends_at IS NULL OR s.ends_at > NOW())"
        );

        return $stmt->fetchAll();
    }

    public function recordAccrual(int $subscriptionId, int $ledgerEntryId, string $amount, string $date): bool
    {
        $stmt = Database::connection()->prepare(
            'INSERT IGNORE INTO investment_accrual_logs (subscription_id, ledger_entry_id, amount, accrual_date)
             VALUES (:subscription_id, :ledger_entry_id, :amount, :date)'
        );
        $stmt->execute([
            'subscription_id' => $subscriptionId,
            'ledger_entry_id' => $ledgerEntryId,
            'amount' => $amount,
            'date' => $date,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function bumpAccruedTotal(int $subscriptionId, string $amount): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE investment_subscriptions SET total_accrued = total_accrued + :amount, last_accrued_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['amount' => $amount, 'id' => $subscriptionId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function referredUsers(int $referrerUserId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT r.created_at, u.first_name, u.last_name, u.email
             FROM referral_relationships r
             JOIN users u ON u.id = r.referred_user_id
             WHERE r.referrer_user_id = :referrer_user_id
             ORDER BY r.created_at DESC'
        );
        $stmt->execute(['referrer_user_id' => $referrerUserId]);

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function commissionsForUser(int $referrerUserId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT c.*, u.first_name, u.last_name
             FROM referral_commissions c
             JOIN users u ON u.id = c.referred_user_id
             WHERE c.referrer_user_id = :referrer_user_id
             ORDER BY c.created_at DESC'
        );
        $stmt->execute(['referrer_user_id' => $referrerUserId]);

        return $stmt->fetchAll();
    }
}
