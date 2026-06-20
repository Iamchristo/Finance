<?php $this->layout('layouts/dashboard-realestate', ['title' => 'Real Estate Overview']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Real Estate Overview</h1>

<?= $this->insert('partials/wallet-balances', ['balances' => $balances]) ?>

<div class="flex gap-3 mb-6">
    <a href="/dashboard/realestate/properties" class="btn-primary">Browse Properties</a>
    <a href="/dashboard/realestate/marketplace" class="btn-secondary">Marketplace</a>
</div>

<h2 class="text-lg font-semibold text-white mb-3">My Portfolio</h2>
<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr><th class="text-left px-4 py-3">Property</th><th class="text-right px-4 py-3">Shares</th><th class="text-right px-4 py-3">Invested</th><th class="text-left px-4 py-3">Status</th></tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($portfolio as $p): ?>
                <tr>
                    <td class="px-4 py-3"><?= e($p['title']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e((string) $p['shares_purchased']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e(money($p['amount_invested'])) ?></td>
                    <td class="px-4 py-3 capitalize"><?= e($p['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($portfolio === []): ?>
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No investments yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
