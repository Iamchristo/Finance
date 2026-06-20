<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Exceptions\ValidationException;
use App\Core\Session;
use App\Enums\WalletSection;
use App\Models\User;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly WalletService $wallets,
        private readonly AuditService $audit,
    ) {
    }

    public function register(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        ?string $referralCode,
        string $ip,
    ): User {
        if ($this->users->findByEmail($email) !== null) {
            throw new ValidationException(['email' => 'An account with this email already exists.']);
        }

        $referredBy = null;
        if ($referralCode !== null && $referralCode !== '') {
            $referrer = $this->users->findByReferralCode($referralCode);
            $referredBy = $referrer?->id;
        }

        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        $user = Database::transaction(function () use ($firstName, $lastName, $email, $passwordHash, $referredBy): User {
            $user = $this->users->create($firstName, $lastName, $email, $passwordHash, $referredBy);
            $this->wallets->ensureWalletExists($user->id);

            if ($referredBy !== null) {
                $stmt = Database::connection()->prepare(
                    'INSERT INTO referral_relationships (referrer_user_id, referred_user_id) VALUES (:referrer, :referred)'
                );
                $stmt->execute(['referrer' => $referredBy, 'referred' => $user->id]);
            }

            return $user;
        });

        $this->audit->log($user->id, 'user', 'user.register', 'user', $user->id, ['ip' => $ip]);

        return $user;
    }

    public function attempt(string $email, string $password, string $ip): User
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || !password_verify($password, $user->passwordHash)) {
            throw new ValidationException(['email' => 'Invalid email or password.']);
        }

        if ($user->status !== 'active') {
            throw new ValidationException(['email' => 'This account is not active. Contact support.']);
        }

        Session::regenerate();
        Session::put('user_id', $user->id);
        Session::put('role', $user->roleName);
        Session::put('full_name', $user->fullName());

        $this->users->recordLogin($user->id, $ip);
        $this->audit->log($user->id, 'user', 'user.login', 'user', $user->id, ['ip' => $ip]);

        return $user;
    }

    public function logout(int $userId): void
    {
        $this->audit->log($userId, 'user', 'user.logout', 'user', $userId);
        Session::destroy();
    }
}
