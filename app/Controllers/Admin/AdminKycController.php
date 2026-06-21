<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\KycRepository;
use App\Services\Admin\KycReviewService;

final class AdminKycController extends Controller
{
    public function __construct(
        private readonly KycRepository $kyc,
        private readonly KycReviewService $review,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->view('admin/kyc/index', [
            'title' => 'KYC Review',
            'pending' => $this->kyc->pending(),
            'all' => $this->kyc->all(),
        ]);
    }

    public function approve(Request $request): Response
    {
        $submissionId = (int) $request->input('submission_id', 0);

        try {
            $this->review->approve($submissionId, (int) $this->currentUserId(), $request);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/admin/kyc');
        }

        Session::flash('success', 'KYC submission approved.');

        return $this->redirect('/admin/kyc');
    }

    public function reject(Request $request): Response
    {
        $submissionId = (int) $request->input('submission_id', 0);
        $reason = (string) $request->input('reason', '');

        try {
            $this->review->reject($submissionId, (int) $this->currentUserId(), $reason, $request);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/admin/kyc');
        }

        Session::flash('success', 'KYC submission rejected.');

        return $this->redirect('/admin/kyc');
    }
}
