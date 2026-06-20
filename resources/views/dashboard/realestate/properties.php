<?php $this->layout('layouts/dashboard-realestate', ['title' => 'Properties']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Properties</h1>

<div class="grid sm:grid-cols-2 gap-4">
    <?php foreach ($properties as $p): ?>
        <div class="card">
            <div class="flex justify-between items-baseline mb-2">
                <h2 class="text-lg font-semibold text-white"><?= e($p['title']) ?></h2>
                <span class="text-xs uppercase font-semibold text-slate-400"><?= e($p['mode']) ?></span>
            </div>
            <p class="text-sm text-slate-400 mb-3"><?= e($p['city']) ?><?= $p['city'] && $p['country'] ? ', ' : '' ?><?= e($p['country']) ?></p>
            <dl class="text-sm space-y-1 mb-4">
                <div class="flex justify-between"><dt class="text-slate-400">Total Value</dt><dd><?= e(money($p['total_value'])) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Share Price</dt><dd><?= e(money($p['share_price'])) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Funded</dt><dd><?= e((string) $p['shares_sold']) ?> / <?= e((string) $p['total_shares']) ?> shares</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Expected ROI</dt><dd class="badge-gain"><?= e($p['expected_annual_roi_percent']) ?>% / yr</dd></div>
            </dl>
            <?php if ($p['mode'] === 'fractional' && $p['funding_status'] === 'open'): ?>
                <form method="POST" action="/dashboard/realestate/invest" class="flex gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="property_id" value="<?= e((string) $p['id']) ?>">
                    <input type="number" name="shares" min="1" required class="input" placeholder="Shares">
                    <button type="submit" class="btn-primary whitespace-nowrap">Invest</button>
                </form>
            <?php else: ?>
                <span class="text-xs text-slate-500 capitalize"><?= e($p['funding_status']) ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if ($properties === []): ?>
        <p class="text-slate-500">No properties are currently available.</p>
    <?php endif; ?>
</div>

<?php $this->stop() ?>
