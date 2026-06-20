<?php $this->layout('layouts/dashboard-investment', ['title' => 'My Subscriptions']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">My Subscriptions</h1>

<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Plan</th>
                <th class="text-right px-4 py-3">Principal</th>
                <th class="text-right px-4 py-3">Accrued</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-left px-4 py-3">Started</th>
                <th class="text-left px-4 py-3">Ends</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td class="px-4 py-3"><?= e($sub['plan_name']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e(money($sub['principal_amount'])) ?></td>
                    <td class="px-4 py-3 text-right badge-gain">+<?= e(money($sub['total_accrued'])) ?></td>
                    <td class="px-4 py-3 capitalize"><?= e($sub['status']) ?></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($sub['starts_at']) ?></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($sub['ends_at'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($subscriptions === []): ?>
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No subscriptions yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
