<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use Ramsey\Uuid\Uuid;

final class UserRepository
{
    private const SELECT = 'SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id';

    public function findById(int $id): ?User
    {
        $stmt = Database::connection()->prepare(self::SELECT . ' WHERE u.id = :id AND u.deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : User::fromRow($row);
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = Database::connection()->prepare(self::SELECT . ' WHERE u.email = :email AND u.deleted_at IS NULL');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row === false ? null : User::fromRow($row);
    }

    public function findByReferralCode(string $code): ?User
    {
        $stmt = Database::connection()->prepare(self::SELECT . ' WHERE u.referral_code = :code AND u.deleted_at IS NULL');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();

        return $row === false ? null : User::fromRow($row);
    }

    public function create(
        string $firstName,
        string $lastName,
        string $email,
        string $passwordHash,
        ?int $referredByUserId = null,
    ): User {
        $pdo = Database::connection();

        $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = :name');
        $roleStmt->execute(['name' => 'user']);
        $roleId = (int) $roleStmt->fetchColumn();

        $stmt = $pdo->prepare(
            'INSERT INTO users (uuid, first_name, last_name, email, password_hash, role_id, referral_code, referred_by_user_id, status)
             VALUES (:uuid, :first_name, :last_name, :email, :password_hash, :role_id, :referral_code, :referred_by_user_id, :status)'
        );
        $stmt->execute([
            'uuid' => Uuid::uuid4()->toString(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role_id' => $roleId,
            'referral_code' => $this->generateUniqueReferralCode(),
            'referred_by_user_id' => $referredByUserId,
            'status' => 'active',
        ]);

        return $this->findById((int) $pdo->lastInsertId());
    }

    public function recordLogin(int $userId, string $ip): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id'
        );
        $stmt->execute(['ip' => $ip, 'id' => $userId]);
    }

    public function updateStatus(int $userId, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $userId]);
    }

    /** @return User[] */
    public function paginate(int $page, int $perPage, ?string $search = null): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $sql = self::SELECT;
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' WHERE (u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search) AND u.deleted_at IS NULL';
            $params['search'] = "%{$search}%";
        } else {
            $sql .= ' WHERE u.deleted_at IS NULL';
        }

        $sql .= ' ORDER BY u.id DESC LIMIT :limit OFFSET :offset';

        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map(User::fromRow(...), $stmt->fetchAll());
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
            $stmt = Database::connection()->prepare('SELECT 1 FROM users WHERE referral_code = :code');
            $stmt->execute(['code' => $code]);
        } while ($stmt->fetchColumn() !== false);

        return $code;
    }
}
