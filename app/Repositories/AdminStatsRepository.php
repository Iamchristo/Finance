<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class AdminStatsRepository
{
    /** @return array<string, mixed> */
    public function overview(): array
    {
        $pdo = Database::connection();

        return [
            'total_users' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn(),
            'pending_kyc' => (int) $pdo->query("SELECT COUNT(*) FROM kyc_submissions WHERE status = 'pending'")->fetchColumn(),
            'pending_listings' => (int) $pdo->query("SELECT COUNT(*) FROM re_listings WHERE status = 'pending_review'")->fetchColumn(),
            'active_subscriptions' => (int) $pdo->query("SELECT COUNT(*) FROM investment_subscriptions WHERE status = 'active'")->fetchColumn(),
            'open_positions' => (int) $pdo->query("SELECT COUNT(*) FROM fx_positions WHERE status = 'open'")->fetchColumn(),
            'active_property_investments' => (int) $pdo->query("SELECT COUNT(*) FROM re_property_investments WHERE status = 'active'")->fetchColumn(),
            'total_main_balance' => (string) $pdo->query('SELECT COALESCE(SUM(main_balance), 0) FROM wallets')->fetchColumn(),
            'total_investment_balance' => (string) $pdo->query('SELECT COALESCE(SUM(investment_balance), 0) FROM wallets')->fetchColumn(),
            'total_forex_balance' => (string) $pdo->query('SELECT COALESCE(SUM(forex_balance), 0) FROM wallets')->fetchColumn(),
            'total_realestate_balance' => (string) $pdo->query('SELECT COALESCE(SUM(realestate_balance), 0) FROM wallets')->fetchColumn(),
        ];
    }
}
