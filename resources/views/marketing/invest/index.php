<?php $this->layout('layouts/app', ['title' => 'High-Yield Investment Plans']) ?>
<?php $this->start('body') ?>

<section class="text-center py-10">
    <div class="text-amber-400 text-sm font-semibold mb-2">High-Yield Investment</div>
    <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">Tiered investment plans for every horizon</h1>
    <p class="text-slate-400 max-w-2xl mx-auto"><?= e($simulatedBanner) ?></p>
</section>

<section class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 py-6">
    <?php foreach ($plans as $plan): ?>
        <a href="/invest/<?= e($plan['slug']) ?>" class="card hover:shadow-elevated transition block">
            <div class="text-xs font-semibold uppercase text-brand-400 mb-2"><?= e($plan['tier']) ?></div>
            <h2 class="text-lg font-bold text-white mb-2"><?= e($plan['name']) ?></h2>
            <p class="text-sm text-slate-400 mb-4"><?= e($plan['description'] ?? '') ?></p>
            <div class="text-2xl font-bold text-gain-500 mb-1"><?= e($plan['roi_percent']) ?>%</div>
            <div class="text-xs text-slate-500 mb-3"><?= e($plan['roi_period']) ?> ROI &middot; <?= e((string) $plan['duration_days']) ?> days</div>
            <div class="text-xs text-slate-400">
                Min <?= e(money($plan['min_amount'])) ?>
                <?php if ($plan['max_amount'] !== null): ?>
                    &ndash; Max <?= e(money($plan['max_amount'])) ?>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
    <?php if ($plans === []): ?>
        <p class="text-slate-500 col-span-full text-center py-8">No plans are currently available.</p>
    <?php endif; ?>
</section>

<section class="card mt-4 text-center">
    <h2 class="text-xl font-bold text-white mb-2">Ready to subscribe?</h2>
    <p class="text-sm text-slate-400 mb-4">Create an account, fund your wallet, and subscribe to a plan from your investment dashboard.</p>
    <a href="/register" class="btn-primary">Get Started</a>
</section>

<?php $this->stop() ?>
