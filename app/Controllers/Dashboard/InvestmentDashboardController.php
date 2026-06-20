<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use App\Core\Controller;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\InvestmentRepository;
use App\Repositories\UserRepository;
use App\Services\Investment\SubscriptionService;
use App\Services\WalletService;

final class InvestmentDashboardController extends Controller
{
    public function __construct(
        private readonly InvestmentRepository $investments,
        private readonly SubscriptionService $subscriptions,
        private readonly WalletService $wallets,
        private readonly UserRepository $users,
    ) {
    }

    public function overview(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('dashboard/investment/overview', [
            'title' => 'Investment Overview',
            'balances' => $this->wallets->getBalances($userId),
            'subscriptions' => $this->investments->subscriptionsForUser($userId),
        ]);
    }

    public function plans(Request $request): Response
    {
        return $this->view('dashboard/investment/plans', [
            'title' => 'Investment Plans',
            'plans' => $this->investments->activePlans(),
        ]);
    }

    public function subscribe(Request $request): Response
    {
        $userId = $this->currentUserId();
        $planId = (int) $request->input('plan_id', 0);
        $amount = (string) $request->input('amount', '0');

        try {
            $this->subscriptions->subscribe($userId, $planId, $amount);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/dashboard/investment/plans');
        }

        Session::flash('success', 'Subscription created successfully.');

        return $this->redirect('/dashboard/investment/subscriptions');
    }

    public function subscriptions(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('dashboard/investment/subscriptions', [
            'title' => 'My Subscriptions',
            'subscriptions' => $this->investments->subscriptionsForUser($userId),
        ]);
    }

    public function referrals(Request $request): Response
    {
        $userId = $this->currentUserId();
        $user = $this->users->findById($userId);

        return $this->view('dashboard/investment/referrals', [
            'title' => 'Referrals',
            'referralCode' => $user?->referralCode ?? '',
            'referredUsers' => $this->investments->referredUsers($userId),
            'commissions' => $this->investments->commissionsForUser($userId),
        ]);
    }
}
