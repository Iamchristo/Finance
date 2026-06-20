<?php $this->layout('layouts/app', ['title' => 'Ledger']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Ledger</h1>

<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Date</th>
                <th class="text-left px-4 py-3">Section</th>
                <th class="text-left px-4 py-3">Type</th>
                <th class="text-right px-4 py-3">Amount</th>
                <th class="text-right px-4 py-3">Balance After</th>
                <th class="text-left px-4 py-3">Description</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td class="px-4 py-3 text-slate-400"><?= e($entry['created_at']) ?></td>
                    <td class="px-4 py-3 capitalize"><?= e($entry['wallet_section']) ?></td>
                    <td class="px-4 py-3 text-slate-300"><?= e(str_replace('_', ' ', $entry['type'])) ?></td>
                    <td class="px-4 py-3 text-right <?= $entry['direction'] === 'credit' ? 'badge-gain' : 'badge-loss' ?>">
                        <?= $entry['direction'] === 'credit' ? '+' : '-' ?><?= e(money($entry['amount'])) ?>
                    </td>
                    <td class="px-4 py-3 text-right text-slate-300"><?= e(money($entry['balance_after'])) ?></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($entry['description'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($entries === []): ?>
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No ledger activity yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
