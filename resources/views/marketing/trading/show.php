<?php $this->layout('layouts/app', ['title' => $instrument['display_name']]) ?>
<?php $this->start('body') ?>

<a href="/trading" class="text-sm text-slate-400 hover:text-white">&larr; All instruments</a>

<section class="card mt-4">
    <div class="text-xs font-semibold uppercase text-sky-400 mb-2"><?= e($instrument['class_label']) ?></div>
    <h1 class="text-3xl font-bold text-white mb-1"><?= e($instrument['display_name']) ?></h1>
    <div class="text-slate-500 text-sm mb-6"><?= e($instrument['symbol']) ?> &middot; <?= e($instrument['base_currency']) ?>/<?= e($instrument['quote_currency']) ?></div>

    <div class="grid sm:grid-cols-4 gap-4 mb-6">
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Price</div>
            <div class="text-2xl font-bold text-white"><?= e(money($instrument['current_price'])) ?></div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">24h Change</div>
            <div class="text-2xl font-bold <?= ((float) $instrument['daily_change_percent']) >= 0 ? 'badge-gain' : 'badge-loss' ?>">
                <?= e((string) $instrument['daily_change_percent']) ?>%
            </div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Max Leverage</div>
            <div class="text-2xl font-bold text-white"><?= e((string) $instrument['leverage_max']) ?>x</div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Min Trade Size</div>
            <div class="text-2xl font-bold text-white"><?= e($instrument['min_trade_size']) ?></div>
        </div>
    </div>

    <a href="/register" class="btn-primary">Trade on Dashboard</a>
</section>

<p class="text-xs text-slate-500 mt-6"><?= e($simulatedBanner) ?> Prices are produced by an internal simulator and do not reflect real markets.</p>

<?php $this->stop() ?>
