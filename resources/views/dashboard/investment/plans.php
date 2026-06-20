<?php $this->layout('layouts/dashboard-investment', ['title' => 'Investment Plans']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Investment Plans</h1>

<div class="grid sm:grid-cols-2 gap-4">
    <?php foreach ($plans as $plan): ?>
        <div class="card">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold text-white"><?= e($plan['name']) ?></h2>
                <span class="badge-gain text-xs uppercase font-semibold"><?= e($plan['tier']) ?></span>
            </div>
            <p class="text-sm text-slate-400 mb-4"><?= e($plan['description'] ?? '') ?></p>
            <dl class="text-sm space-y-1 mb-4">
                <div class="flex justify-between"><dt class="text-slate-400">ROI</dt><dd><?= e($plan['roi_percent']) ?>% / <?= e($plan['roi_period']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Duration</dt><dd><?= e((string) $plan['duration_days']) ?> days</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Min</dt><dd><?= e(money($plan['min_amount'])) ?></dd></div>
                <?php if ($plan['max_amount'] !== null): ?>
                    <div class="flex justify-between"><dt class="text-slate-400">Max</dt><dd><?= e(money($plan['max_amount'])) ?></dd></div>
                <?php endif; ?>
            </dl>
            <form method="POST" action="/dashboard/investment/subscribe" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="plan_id" value="<?= e((string) $plan['id']) ?>">
                <input type="text" name="amount" inputmode="decimal" required class="input" placeholder="Amount">
                <button type="submit" class="btn-primary whitespace-nowrap">Subscribe</button>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if ($plans === []): ?>
        <p class="text-slate-500">No plans are currently available.</p>
    <?php endif; ?>
</div>

<?php $this->stop() ?>
