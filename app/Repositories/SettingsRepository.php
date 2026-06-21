<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class SettingsRepository
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM settings ORDER BY setting_key');

        return $stmt->fetchAll();
    }

    public function get(string $key): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM settings WHERE setting_key = :key');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function upsert(string $key, string $value, string $type = 'string'): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO settings (setting_key, setting_value, setting_type)
             VALUES (:key, :value, :type)
             ON DUPLICATE KEY UPDATE setting_value = :value_update, setting_type = :type_update'
        );
        $stmt->execute([
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'value_update' => $value,
            'type_update' => $type,
        ]);
    }
}
