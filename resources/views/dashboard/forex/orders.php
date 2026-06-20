<?php $this->layout('layouts/dashboard-forex', ['title' => 'Order History']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Order History</h1>

<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Symbol</th>
                <th class="text-left px-4 py-3">Side</th>
                <th class="text-left px-4 py-3">Type</th>
                <th class="text-right px-4 py-3">Qty</th>
                <th class="text-right px-4 py-3">Filled Price</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-left px-4 py-3">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="px-4 py-3 font-medium"><?= e($o['symbol']) ?></td>
                    <td class="px-4 py-3 capitalize <?= $o['side'] === 'buy' ? 'badge-gain' : 'badge-loss' ?>"><?= e($o['side']) ?></td>
                    <td class="px-4 py-3 capitalize text-slate-400"><?= e($o['order_type']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e($o['quantity']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e($o['filled_price'] ?? '—') ?></td>
                    <td class="px-4 py-3 capitalize"><?= e($o['status']) ?></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($o['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($orders === []): ?>
                <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No orders yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
