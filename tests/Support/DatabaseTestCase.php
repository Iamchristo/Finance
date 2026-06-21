<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Core\Database;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Wraps each test in one real transaction that is rolled back in tearDown,
 * so tests can exercise the real Database::transaction()/savepoint machinery
 * against the live schema without leaving any residue behind.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::beginTestTransaction();
    }

    protected function tearDown(): void
    {
        Database::rollbackTestTransaction();
        parent::tearDown();
    }

    protected function createTestUser(string $emailPrefix = 'wallet_test'): int
    {
        $pdo = Database::connection();
        $suffix = bin2hex(random_bytes(6));
        $email = $emailPrefix . '_' . $suffix . '@example.test';

        $stmt = $pdo->prepare(
            "INSERT INTO users (uuid, email, password_hash, first_name, last_name, role_id, status, referral_code)
             VALUES (:uuid, :email, 'unused', 'Test', 'User', (SELECT id FROM roles WHERE name = 'user'), 'active', :referral_code)"
        );
        $stmt->execute([
            'uuid' => Uuid::uuid4()->toString(),
            'email' => $email,
            'referral_code' => strtoupper(substr($suffix, 0, 10)),
        ]);
        $userId = (int) $pdo->lastInsertId();

        $pdo->prepare('INSERT INTO wallets (user_id) VALUES (:user_id)')->execute(['user_id' => $userId]);

        return $userId;
    }
}
