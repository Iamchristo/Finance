<?php

declare(strict_types=1);

namespace App\Controllers\Wallet;

use App\Core\Controller;
use App\Core\Exceptions\InsufficientBalanceException;
use App\Enums\WalletSection;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\WalletRepository;
use App\Services\WalletService;
use RuntimeException;

final class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly WalletRepository $walletRepository,
    ) {
    }

    public function overview(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('wallet/overview', [
            'title' => 'Wallet',
            'balances' => $this->wallets->getBalances($userId),
        ]);
    }

    public function transferForm(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('wallet/transfer', [
            'title' => 'Transfer Funds',
            'balances' => $this->wallets->getBalances($userId),
        ]);
    }

    public function transfer(Request $request): Response
    {
        $userId = $this->currentUserId();
        $from = WalletSection::tryFrom((string) $request->input('from', ''));
        $to = WalletSection::tryFrom((string) $request->input('to', ''));
        $amount = (string) $request->input('amount', '0');

        if ($from === null || $to === null) {
            Session::flash('error', 'Please choose valid wallet sections.');

            return $this->redirect('/wallet/transfer');
        }

        try {
            $this->wallets->transfer($userId, $from, $to, $amount);
        } catch (InsufficientBalanceException|RuntimeException $e) {
            Session::flash('error', $e->getMessage());

            return $this->redirect('/wallet/transfer');
        }

        Session::flash('success', "Transferred {$amount} from {$from->label()} to {$to->label()}.");

        return $this->redirect('/wallet');
    }

    public function ledger(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('wallet/ledger', [
            'title' => 'Ledger',
            'entries' => $this->walletRepository->ledgerForUser($userId),
        ]);
    }
}
