<?php $this->layout('layouts/dashboard-realestate', ['title' => 'My Portfolio']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">My Portfolio</h1>

<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Property</th>
                <th class="text-right px-4 py-3">Shares</th>
                <th class="text-right px-4 py-3">Invested</th>
                <th class="text-right px-4 py-3">Expected ROI</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-left px-4 py-3">Invested On</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($portfolio as $p): ?>
                <tr>
                    <td class="px-4 py-3"><?= e($p['title']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e((string) $p['shares_purchased']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e(money($p['amount_invested'])) ?></td>
                    <td class="px-4 py-3 text-right badge-gain"><?= e($p['expected_annual_roi_percent']) ?>%</td>
                    <td class="px-4 py-3 capitalize"><?= e($p['status']) ?></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($p['invested_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($portfolio === []): ?>
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No investments yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
