<?php $this->layout('layouts/app', ['title' => 'Calculators']) ?>
<?php $this->start('body') ?>

<section class="text-center py-10">
    <h1 class="text-3xl font-bold text-white tracking-tight mb-4">Calculators</h1>
    <p class="text-slate-400 max-w-2xl mx-auto"><?= e($simulatedBanner) ?> These tools illustrate the underlying math only.</p>
</section>

<section class="grid lg:grid-cols-2 gap-6">
    <div class="card" x-data="{
        principal: 1000,
        roi: 2.5,
        periods: 30,
        compounding: false,
        get result() {
            if (!this.compounding) {
                return this.principal + (this.principal * (this.roi / 100) * this.periods);
            }
            return this.principal * Math.pow(1 + (this.roi / 100), this.periods);
        },
        get profit() { return this.result - this.principal; },
    }">
        <h2 class="text-lg font-bold text-white mb-4">Investment ROI Calculator</h2>
        <div class="space-y-3">
            <label class="block text-sm text-slate-400">Principal Amount
                <input type="number" min="0" step="0.01" x-model.number="principal" class="input mt-1">
            </label>
            <label class="block text-sm text-slate-400">ROI per Period (%)
                <input type="number" min="0" step="0.01" x-model.number="roi" class="input mt-1">
            </label>
            <label class="block text-sm text-slate-400">Number of Periods
                <input type="number" min="0" step="1" x-model.number="periods" class="input mt-1">
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-400">
                <input type="checkbox" x-model="compounding">
                Compounding (reinvest each period)
            </label>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div>
                <div class="text-xs text-slate-500">Projected Total</div>
                <div class="text-2xl font-bold text-white" x-text="'$' + result.toFixed(2)"></div>
            </div>
            <div>
                <div class="text-xs text-slate-500">Simulated Profit</div>
                <div class="text-2xl font-bold text-gain-500" x-text="'$' + profit.toFixed(2)"></div>
            </div>
        </div>
    </div>

    <div class="card" x-data="{
        margin: 500,
        leverage: 10,
        entryPrice: 100,
        exitPrice: 105,
        get positionSize() { return this.margin * this.leverage; },
        get units() { return this.entryPrice > 0 ? this.positionSize / this.entryPrice : 0; },
        get pnl() { return this.units * (this.exitPrice - this.entryPrice); },
        get pnlPercentOfMargin() { return this.margin > 0 ? (this.pnl / this.margin) * 100 : 0; },
    }">
        <h2 class="text-lg font-bold text-white mb-4">Leverage &amp; Margin Calculator</h2>
        <div class="space-y-3">
            <label class="block text-sm text-slate-400">Margin Used
                <input type="number" min="0" step="0.01" x-model.number="margin" class="input mt-1">
            </label>
            <label class="block text-sm text-slate-400">Leverage (x)
                <input type="number" min="1" step="1" x-model.number="leverage" class="input mt-1">
            </label>
            <label class="block text-sm text-slate-400">Entry Price
                <input type="number" min="0" step="0.0001" x-model.number="entryPrice" class="input mt-1">
            </label>
            <label class="block text-sm text-slate-400">Exit Price
                <input type="number" min="0" step="0.0001" x-model.number="exitPrice" class="input mt-1">
            </label>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div>
                <div class="text-xs text-slate-500">Position Size</div>
                <div class="text-xl font-bold text-white" x-text="'$' + positionSize.toFixed(2)"></div>
            </div>
            <div>
                <div class="text-xs text-slate-500">Simulated P&amp;L</div>
                <div class="text-xl font-bold" :class="pnl >= 0 ? 'badge-gain' : 'badge-loss'" x-text="'$' + pnl.toFixed(2) + ' (' + pnlPercentOfMargin.toFixed(1) + '%)'"></div>
            </div>
        </div>
    </div>
</section>

<?php $this->stop() ?>
