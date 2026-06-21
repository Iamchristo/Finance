<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $host = Config::get('database.host');
            $port = Config::get('database.port');
            $name = Config::get('database.database');
            $charset = Config::get('database.charset', 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

            try {
                self::$connection = new PDO(
                    $dsn,
                    Config::get('database.username'),
                    Config::get('database.password'),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage(), previous: $e);
            }
        }

        return self::$connection;
    }

    /**
     * Run a callback inside a transaction. Supports nested calls via savepoints
     * so services can compose other services without double-committing.
     *
     * @template T
     * @param callable(PDO): T $callback
     * @return T
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        $level = self::transactionLevel($pdo);

        if ($level === 0) {
            $pdo->beginTransaction();
        } else {
            $pdo->exec('SAVEPOINT trans_' . $level);
        }

        self::$transactionLevels[spl_object_id($pdo)] = $level + 1;

        try {
            $result = $callback($pdo);

            self::$transactionLevels[spl_object_id($pdo)] = $level;

            if ($level === 0) {
                $pdo->commit();
            } else {
                $pdo->exec('RELEASE SAVEPOINT trans_' . $level);
            }

            return $result;
        } catch (\Throwable $e) {
            self::$transactionLevels[spl_object_id($pdo)] = $level;

            if ($level === 0) {
                $pdo->rollBack();
            } else {
                $pdo->exec('ROLLBACK TO SAVEPOINT trans_' . $level);
            }

            throw $e;
        }
    }

    /** @var array<int, int> */
    private static array $transactionLevels = [];

    private static function transactionLevel(PDO $pdo): int
    {
        return self::$transactionLevels[spl_object_id($pdo)] ?? 0;
    }

    /**
     * Test-only helpers: open one real transaction and seed the nesting counter
     * so that every Database::transaction() call made during the test becomes a
     * savepoint instead of a real commit, then roll everything back at once.
     */
    public static function beginTestTransaction(): void
    {
        $pdo = self::connection();
        $pdo->beginTransaction();
        self::$transactionLevels[spl_object_id($pdo)] = 1;
    }

    public static function rollbackTestTransaction(): void
    {
        $pdo = self::connection();
        self::$transactionLevels[spl_object_id($pdo)] = 0;
        $pdo->rollBack();
    }
}
