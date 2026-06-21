<?php $this->layout('layouts/app', ['title' => $property['title']]) ?>
<?php $this->start('body') ?>

<a href="/real-estate" class="text-sm text-slate-400 hover:text-white">&larr; All properties</a>

<section class="card mt-4">
    <div class="text-xs font-semibold uppercase text-violet-400 mb-2"><?= e($property['property_type']) ?> &middot; <?= e($property['mode']) ?></div>
    <h1 class="text-3xl font-bold text-white mb-1"><?= e($property['title']) ?></h1>
    <div class="text-slate-500 text-sm mb-6">
        <?= e($property['address'] ?? '') ?><?= $property['address'] ? ', ' : '' ?><?= e($property['city'] ?? '') ?><?= ($property['city'] && $property['country']) ? ', ' : '' ?><?= e($property['country'] ?? '') ?>
    </div>
    <p class="text-slate-400 mb-6"><?= e($property['description'] ?? '') ?></p>

    <div class="grid sm:grid-cols-4 gap-4 mb-6">
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Total Value</div>
            <div class="text-xl font-bold text-white"><?= e(money($property['total_value'])) ?></div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Share Price</div>
            <div class="text-xl font-bold text-white"><?= e(money($property['share_price'])) ?></div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Funding</div>
            <div class="text-xl font-bold text-white"><?= e((string) $property['shares_sold']) ?>/<?= e((string) $property['total_shares']) ?></div>
            <div class="text-xs text-slate-500 capitalize"><?= e($property['funding_status']) ?></div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Annual ROI</div>
            <div class="text-xl font-bold text-gain-500"><?= e($property['expected_annual_roi_percent']) ?>%</div>
        </div>
    </div>

    <a href="/register" class="btn-primary">Invest via Dashboard</a>
</section>

<p class="text-xs text-slate-500 mt-6"><?= e($simulatedBanner) ?> Property values, shares, and returns are simulated for demonstration only.</p>

<?php $this->stop() ?>
