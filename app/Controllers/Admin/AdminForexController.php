<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\ForexRepository;
use App\Services\AuditService;

final class AdminForexController extends Controller
{
    public function __construct(
        private readonly ForexRepository $forex,
        private readonly AuditService $audit,
    ) {
    }

    public function instruments(Request $request): Response
    {
        return $this->view('admin/forex/instruments', [
            'title' => 'Forex Instruments',
            'instruments' => $this->forex->allInstruments(),
            'classes' => $this->forex->instrumentClasses(),
        ]);
    }

    public function createInstrument(Request $request): Response
    {
        $id = $this->forex->createInstrument(
            (int) $request->input('class_id', 0),
            (string) $request->input('symbol', ''),
            (string) $request->input('display_name', ''),
            (string) $request->input('base_currency', ''),
            (string) $request->input('quote_currency', ''),
            (string) $request->input('current_price', '0'),
            (int) $request->input('leverage_max', 1),
            (string) $request->input('min_trade_size', '0.01'),
            (int) $request->input('price_precision', 5),
            (int) $request->input('sort_order', 0),
        );

        $this->audit->log((int) $this->currentUserId(), 'admin', 'fx_instrument.create', 'fx_instrument', $id, [], $request);

        Session::flash('success', 'Instrument created.');

        return $this->redirect('/admin/forex/instruments');
    }

    public function updateInstrument(Request $request): Response
    {
        $id = (int) $request->input('id', 0);

        $this->forex->updateInstrument(
            $id,
            (string) $request->input('display_name', ''),
            (string) $request->input('base_currency', ''),
            (string) $request->input('quote_currency', ''),
            (int) $request->input('leverage_max', 1),
            (string) $request->input('min_trade_size', '0.01'),
            (int) $request->input('price_precision', 5),
            (int) $request->input('sort_order', 0),
        );

        $this->audit->log((int) $this->currentUserId(), 'admin', 'fx_instrument.update', 'fx_instrument', $id, [], $request);

        Session::flash('success', 'Instrument updated.');

        return $this->redirect('/admin/forex/instruments');
    }

    public function toggleInstrumentActive(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $this->forex->toggleInstrumentActive($id);
        $this->audit->log((int) $this->currentUserId(), 'admin', 'fx_instrument.toggle_active', 'fx_instrument', $id, [], $request);

        Session::flash('success', 'Instrument status toggled.');

        return $this->redirect('/admin/forex/instruments');
    }

    public function strategies(Request $request): Response
    {
        return $this->view('admin/forex/strategies', [
            'title' => 'Forex Strategies',
            'strategies' => $this->forex->allStrategies(),
        ]);
    }

    public function createStrategy(Request $request): Response
    {
        $id = $this->forex->createStrategy(
            (string) $request->input('name', ''),
            (string) $request->input('slug', ''),
            (string) $request->input('description', ''),
            (string) $request->input('risk_level', 'medium'),
            (string) $request->input('simulated_monthly_return_percent', '0'),
        );

        $this->audit->log((int) $this->currentUserId(), 'admin', 'fx_strategy.create', 'fx_strategy', $id, [], $request);

        Session::flash('success', 'Strategy created.');

        return $this->redirect('/admin/forex/strategies');
    }

    public function updateStrategy(Request $request): Response
    {
        $id = (int) $request->input('id', 0);

        $this->forex->updateStrategy(
            $id,
            (string) $request->input('name', ''),
            (string) $request->input('description', ''),
            (string) $request->input('risk_level', 'medium'),
            (string) $request->input('simulated_monthly_return_percent', '0'),
        );

        $this->audit->log((int) $this->currentUserId(), 'admin', 'fx_strategy.update', 'fx_strategy', $id, [], $request);

        Session::flash('success', 'Strategy updated.');

        return $this->redirect('/admin/forex/strategies');
    }

    public function toggleStrategyActive(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $this->forex->toggleStrategyActive($id);
        $this->audit->log((int) $this->currentUserId(), 'admin', 'fx_strategy.toggle_active', 'fx_strategy', $id, [], $request);

        Session::flash('success', 'Strategy status toggled.');

        return $this->redirect('/admin/forex/strategies');
    }
}
