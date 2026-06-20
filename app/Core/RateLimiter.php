<?php

declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;

final class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $cutoff = (new DateTimeImmutable())->modify("-{$decaySeconds} seconds")->format('Y-m-d H:i:s');

        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM rate_limit_hits WHERE bucket_key = :key AND created_at > :cutoff'
        );
        $stmt->execute(['key' => $key, 'cutoff' => $cutoff]);

        return (int) $stmt->fetchColumn() >= $maxAttempts;
    }

    public static function hit(string $key): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO rate_limit_hits (bucket_key) VALUES (:key)');
        $stmt->execute(['key' => $key]);
    }
}
