<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\InvestmentRepository;
use App\Services\AuditService;

final class AdminInvestmentController extends Controller
{
    public function __construct(
        private readonly InvestmentRepository $investments,
        private readonly AuditService $audit,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->view('admin/investment/plans', [
            'title' => 'Investment Plans',
            'plans' => $this->investments->allPlans(),
        ]);
    }

    public function create(Request $request): Response
    {
        $id = $this->investments->createPlan(
            (string) $request->input('name', ''),
            (string) $request->input('slug', ''),
            (string) $request->input('description', ''),
            (string) $request->input('tier', 'starter'),
            (string) $request->input('min_amount', '0'),
            $request->input('max_amount') !== null && $request->input('max_amount') !== '' ? (string) $request->input('max_amount') : null,
            (string) $request->input('roi_percent', '0'),
            (string) $request->input('roi_period', 'monthly'),
            (int) $request->input('duration_days', 30),
            (bool) $request->input('compounding_allowed', false),
            (int) $request->input('sort_order', 0),
        );

        $this->audit->log((int) $this->currentUserId(), 'admin', 'investment_plan.create', 'investment_plan', $id, [], $request);

        Session::flash('success', 'Investment plan created.');

        return $this->redirect('/admin/investment/plans');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->input('id', 0);

        $this->investments->updatePlan(
            $id,
            (string) $request->input('name', ''),
            (string) $request->input('description', ''),
            (string) $request->input('tier', 'starter'),
            (string) $request->input('min_amount', '0'),
            $request->input('max_amount') !== null && $request->input('max_amount') !== '' ? (string) $request->input('max_amount') : null,
            (string) $request->input('roi_percent', '0'),
            (string) $request->input('roi_period', 'monthly'),
            (int) $request->input('duration_days', 30),
            (bool) $request->input('compounding_allowed', false),
            (int) $request->input('sort_order', 0),
        );

        $this->audit->log((int) $this->currentUserId(), 'admin', 'investment_plan.update', 'investment_plan', $id, [], $request);

        Session::flash('success', 'Investment plan updated.');

        return $this->redirect('/admin/investment/plans');
    }

    public function toggleActive(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $this->investments->togglePlanActive($id);
        $this->audit->log((int) $this->currentUserId(), 'admin', 'investment_plan.toggle_active', 'investment_plan', $id, [], $request);

        Session::flash('success', 'Investment plan status toggled.');

        return $this->redirect('/admin/investment/plans');
    }
}
