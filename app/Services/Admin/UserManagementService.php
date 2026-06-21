<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Repositories\UserRepository;
use App\Services\AuditService;

final class UserManagementService
{
    private const VALID_STATUSES = ['active', 'suspended', 'banned', 'pending_verification'];

    public function __construct(
        private readonly UserRepository $users,
        private readonly AuditService $audit,
    ) {
    }

    public function setStatus(int $adminUserId, int $targetUserId, string $status, ?Request $request = null): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new ValidationException(['status' => 'Invalid status.']);
        }

        $this->users->updateStatus($targetUserId, $status);
        $this->audit->log($adminUserId, 'admin', 'user.status_change', 'user', $targetUserId, ['status' => $status], $request);
    }

    public function assignRole(int $adminUserId, int $targetUserId, int $roleId, ?Request $request = null): void
    {
        $this->users->updateRole($targetUserId, $roleId);
        $this->audit->log($adminUserId, 'admin', 'user.role_assign', 'user', $targetUserId, ['role_id' => $roleId], $request);
    }
}
