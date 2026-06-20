<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Request;

final class AuditService
{
    public function log(
        ?int $actorUserId,
        string $actorType,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $metadata = [],
        ?Request $request = null,
    ): void {
        $stmt = Database::connection()->prepare(
            'INSERT INTO audit_log (actor_user_id, actor_type, action, subject_type, subject_id, metadata, ip_address, user_agent)
             VALUES (:actor_user_id, :actor_type, :action, :subject_type, :subject_id, :metadata, :ip_address, :user_agent)'
        );

        $stmt->execute([
            'actor_user_id' => $actorUserId,
            'actor_type' => $actorType,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => $metadata === [] ? null : json_encode($metadata),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->header('User-Agent'),
        ]);
    }
}
