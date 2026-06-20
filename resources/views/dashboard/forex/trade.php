<?php $this->layout('layouts/dashboard-forex', ['title' => $instrument['symbol'] . ' Trade']) ?>
<?php $this->start('body') ?>

<div class="flex items-baseline justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white"><?= e($instrument['symbol']) ?></h1>
        <p class="text-sm text-slate-400"><?= e($instrument['display_name']) ?> · <?= e($instrument['class_label']) ?></p>
    </div>
    <div class="text-right">
        <div class="text-2xl font-semibold text-white"><?= e($instrument['current_price']) ?></div>
        <div class="<?= $instrument['daily_change_percent'] >= 0 ? 'badge-gain' : 'badge-loss' ?> text-sm"><?= e((string) $instrument['daily_change_percent']) ?>%</div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card !p-2">
        <div id="forex-chart" data-candles='<?= e(json_encode($candles)) ?>'></div>
    </div>

    <div class="card">
        <h2 class="text-lg font-semibold text-white mb-4">Place Order</h2>
        <form method="POST" action="/dashboard/forex/order" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="instrument_id" value="<?= e((string) $instrument['id']) ?>">

            <div class="grid grid-cols-2 gap-2">
                <button type="submit" name="side" value="buy" class="btn-primary !bg-gain-500 hover:!bg-emerald-600">Buy / Long</button>
                <button type="submit" name="side" value="sell" class="btn-primary !bg-loss-500 hover:!bg-rose-600">Sell / Short</button>
            </div>

            <div>
                <label class="block text-sm text-slate-300 mb-1">Quantity</label>
                <input type="text" name="quantity" inputmode="decimal" required class="input" placeholder="<?= e($instrument['min_trade_size']) ?> min">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Leverage (max <?= e((string) $instrument['leverage_max']) ?>x)</label>
                <input type="number" name="leverage" min="1" max="<?= e((string) $instrument['leverage_max']) ?>" value="1" required class="input">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Stop Loss</label>
                    <input type="text" name="stop_loss" inputmode="decimal" class="input">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Take Profit</label>
                    <input type="text" name="take_profit" inputmode="decimal" class="input">
                </div>
            </div>
            <p class="text-xs text-slate-500">Orders fill instantly at the simulated market price. No real funds are involved.</p>
        </form>
    </div>
</div>

<script type="module" src="<?= e(vite_asset('resources/js/charts/forex-chart.js')) ?>"></script>

<?php $this->stop() ?>
