<?php $this->layout('layouts/dashboard-forex', ['title' => 'Trading Overview']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Trading Overview</h1>

<?= $this->insert('partials/wallet-balances', ['balances' => $balances]) ?>

<div class="flex gap-3 mb-6">
    <a href="/dashboard/forex/markets" class="btn-primary">Browse Markets</a>
    <a href="/wallet/transfer" class="btn-secondary">Fund Trading Wallet</a>
</div>

<h2 class="text-lg font-semibold text-white mb-3">Open Positions</h2>
<div class="card !p-0 overflow-hidden mb-8">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Symbol</th>
                <th class="text-left px-4 py-3">Side</th>
                <th class="text-right px-4 py-3">Qty</th>
                <th class="text-right px-4 py-3">Entry</th>
                <th class="text-right px-4 py-3">Current</th>
                <th class="text-right px-4 py-3">Leverage</th>
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
                </tr>
            <?php endforeach; ?>
            <?php if ($positions === []): ?>
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No open positions.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 class="text-lg font-semibold text-white mb-3">Markets</h2>
<div class="grid sm:grid-cols-3 gap-3">
    <?php foreach (array_slice($instruments, 0, 9) as $i): ?>
        <a href="/dashboard/forex/trade/<?= e($i['symbol']) ?>" class="card hover:bg-surface-200 transition !p-4">
            <div class="flex justify-between items-baseline">
                <span class="font-semibold"><?= e($i['symbol']) ?></span>
                <span class="<?= $i['daily_change_percent'] >= 0 ? 'badge-gain' : 'badge-loss' ?> text-xs"><?= e((string) $i['daily_change_percent']) ?>%</span>
            </div>
            <div class="text-slate-400 text-sm mt-1"><?= e($i['current_price']) ?></div>
        </a>
    <?php endforeach; ?>
</div>

<?php $this->stop() ?>
