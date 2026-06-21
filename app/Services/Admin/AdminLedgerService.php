<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Enums\WalletSection;
use App\Services\AuditService;
use App\Services\WalletService;

final class AdminLedgerService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly AuditService $audit,
    ) {
    }

    public function adjust(
        int $adminUserId,
        int $targetUserId,
        WalletSection $section,
        string $direction,
        string $amount,
        string $reason,
        ?Request $request = null,
    ): int {
        if (!is_numeric($amount) || bccomp($amount, '0', 8) <= 0) {
            throw new ValidationException(['amount' => 'Amount must be a positive numeric value.']);
        }

        if ($reason === '') {
            throw new ValidationException(['reason' => 'A reason is required for manual ledger adjustments.']);
        }

        $ledgerEntryId = $direction === 'credit'
            ? $this->wallets->credit($targetUserId, $section, $amount, 'admin_adjustment_credit', 'admin_adjustment', null, $reason, $adminUserId)
            : $this->wallets->debit($targetUserId, $section, $amount, 'admin_adjustment_debit', 'admin_adjustment', null, $reason, $adminUserId);

        $this->audit->log(
            $adminUserId,
            'admin',
            "ledger.{$direction}",
            'wallet',
            $targetUserId,
            ['section' => $section->value, 'amount' => $amount, 'reason' => $reason],
            $request,
        );

        return $ledgerEntryId;
    }
}
