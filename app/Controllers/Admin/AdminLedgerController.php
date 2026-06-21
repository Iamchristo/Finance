<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Enums\WalletSection;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Services\Admin\AdminLedgerService;

final class AdminLedgerController extends Controller
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly UserRepository $users,
        private readonly AdminLedgerService $ledger,
    ) {
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->input('page', 1));
        $section = $request->input('section') !== null ? (string) $request->input('section') : null;
        $perPage = 50;

        return $this->view('admin/ledger/index', [
            'title' => 'Ledger Explorer',
            'entries' => $this->walletRepository->paginateLedger($page, $perPage, $section),
            'page' => $page,
            'perPage' => $perPage,
            'totalCount' => $this->walletRepository->countLedger($section),
            'section' => $section ?? '',
            'sections' => WalletSection::cases(),
        ]);
    }

    public function adjustForm(Request $request): Response
    {
        return $this->view('admin/ledger/adjust', [
            'title' => 'Manual Ledger Adjustment',
            'sections' => WalletSection::cases(),
        ]);
    }

    public function adjust(Request $request): Response
    {
        $email = (string) $request->input('email', '');
        $section = WalletSection::tryFrom((string) $request->input('section', ''));
        $direction = (string) $request->input('direction', '');
        $amount = (string) $request->input('amount', '0');
        $reason = (string) $request->input('reason', '');

        $targetUser = $this->users->findByEmail($email);

        if ($targetUser === null || $section === null || !in_array($direction, ['credit', 'debit'], true)) {
            Session::flash('error', 'Please provide a valid user email, section, and direction.');

            return $this->redirect('/admin/ledger/adjust');
        }

        try {
            $this->ledger->adjust((int) $this->currentUserId(), $targetUser->id, $section, $direction, $amount, $reason, $request);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/admin/ledger/adjust');
        }

        Session::flash('success', "Ledger adjustment applied to {$targetUser->email}.");

        return $this->redirect('/admin/ledger');
    }
}
