<?php $this->layout('layouts/app', ['title' => $plan['name']]) ?>
<?php $this->start('body') ?>

<a href="/invest" class="text-sm text-slate-400 hover:text-white">&larr; All plans</a>

<section class="card mt-4">
    <div class="text-xs font-semibold uppercase text-brand-400 mb-2"><?= e($plan['tier']) ?></div>
    <h1 class="text-3xl font-bold text-white mb-3"><?= e($plan['name']) ?></h1>
    <p class="text-slate-400 mb-6"><?= e($plan['description'] ?? '') ?></p>

    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Simulated ROI</div>
            <div class="text-2xl font-bold text-gain-500"><?= e($plan['roi_percent']) ?>%</div>
            <div class="text-xs text-slate-500"><?= e($plan['roi_period']) ?></div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Duration</div>
            <div class="text-2xl font-bold text-white"><?= e((string) $plan['duration_days']) ?></div>
            <div class="text-xs text-slate-500">days</div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-500 mb-1">Contribution range</div>
            <div class="text-lg font-bold text-white">
                <?= e(money($plan['min_amount'])) ?>
                <?php if ($plan['max_amount'] !== null): ?>
                    &ndash; <?= e(money($plan['max_amount'])) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="text-sm text-slate-400 mb-6">
        Compounding: <?= ((int) $plan['compounding_allowed']) === 1 ? 'Allowed — accrued returns can be reinvested into principal.' : 'Not available on this plan.' ?>
    </div>

    <a href="/register" class="btn-primary">Subscribe via Dashboard</a>
</section>

<p class="text-xs text-slate-500 mt-6"><?= e($simulatedBanner) ?> This page is for illustration only and is not investment advice.</p>

<?php $this->stop() ?>
