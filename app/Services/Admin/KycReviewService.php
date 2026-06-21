<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Core\Database;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Repositories\KycRepository;
use App\Repositories\UserRepository;
use App\Services\AuditService;

final class KycReviewService
{
    public function __construct(
        private readonly KycRepository $kyc,
        private readonly UserRepository $users,
        private readonly AuditService $audit,
    ) {
    }

    public function approve(int $submissionId, int $reviewerUserId, ?Request $request = null): void
    {
        $this->decide($submissionId, $reviewerUserId, 'approved', null, $request);
    }

    public function reject(int $submissionId, int $reviewerUserId, string $reason, ?Request $request = null): void
    {
        if ($reason === '') {
            throw new ValidationException(['reason' => 'A rejection reason is required.']);
        }

        $this->decide($submissionId, $reviewerUserId, 'rejected', $reason, $request);
    }

    private function decide(int $submissionId, int $reviewerUserId, string $status, ?string $reason, ?Request $request): void
    {
        $submission = $this->kyc->find($submissionId);

        if ($submission === null) {
            throw new ValidationException(['submission' => 'KYC submission not found.']);
        }

        Database::transaction(function () use ($submission, $submissionId, $reviewerUserId, $status, $reason): void {
            $this->kyc->review($submissionId, $status, $reviewerUserId, $reason);

            $userKycStatus = $status === 'approved' ? 'approved' : 'rejected';
            $pdo = Database::connection();
            $stmt = $pdo->prepare('UPDATE users SET kyc_status = :status WHERE id = :id');
            $stmt->execute(['status' => $userKycStatus, 'id' => $submission['user_id']]);
        });

        $this->audit->log(
            $reviewerUserId,
            'admin',
            "kyc.{$status}",
            'kyc_submission',
            $submissionId,
            $reason !== null ? ['reason' => $reason] : [],
            $request,
        );
    }
}
