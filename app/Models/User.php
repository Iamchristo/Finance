<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly int $roleId,
        public readonly string $roleName,
        public readonly string $status,
        public readonly string $kycStatus,
        public readonly bool $twoFactorEnabled,
        public readonly string $referralCode,
        public readonly ?int $referredByUserId,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            uuid: $row['uuid'],
            firstName: $row['first_name'],
            lastName: $row['last_name'],
            email: $row['email'],
            passwordHash: $row['password_hash'],
            roleId: (int) $row['role_id'],
            roleName: $row['role_name'] ?? '',
            status: $row['status'],
            kycStatus: $row['kyc_status'],
            twoFactorEnabled: (bool) $row['two_factor_enabled'],
            referralCode: $row['referral_code'],
            referredByUserId: isset($row['referred_by_user_id']) ? (int) $row['referred_by_user_id'] : null,
        );
    }

    public function fullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }
}
