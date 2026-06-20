<?php $this->layout('layouts/dashboard-forex', ['title' => 'Open Positions']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Open Positions</h1>

<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Symbol</th>
                <th class="text-left px-4 py-3">Side</th>
                <th class="text-right px-4 py-3">Qty</th>
                <th class="text-right px-4 py-3">Entry</th>
                <th class="text-right px-4 py-3">Current</th>
                <th class="text-right px-4 py-3">Leverage</th>
                <th class="text-right px-4 py-3">Margin</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($positions as $p): ?>
                <tr>
                    <td class="px-4 py-3 font-medium"><?= e($p['symbol']) ?></td>
                    <td class="px-4 py-3 capitalize <?= $p['side'] === 'long' ? 'badge-gain' : 'badge-loss' ?>"><?= e($p['side']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e($p['quantity']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e($p['entry_price']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e($p['current_price']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e((string) $p['leverage']) ?>x</td>
                    <td class="px-4 py-3 text-right"><?= e(money($p['margin_used'])) ?></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="/dashboard/forex/positions/close">
                            <?= csrf_field() ?>
                            <input type="hidden" name="position_id" value="<?= e((string) $p['id']) ?>">
                            <button type="submit" class="btn-secondary !py-1 !px-3">Close</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($positions === []): ?>
                <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">No open positions.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
