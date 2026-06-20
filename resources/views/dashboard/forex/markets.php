<?php $this->layout('layouts/dashboard-forex', ['title' => 'Markets']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Markets</h1>

<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Symbol</th>
                <th class="text-left px-4 py-3">Class</th>
                <th class="text-right px-4 py-3">Price</th>
                <th class="text-right px-4 py-3">24h Change</th>
                <th class="text-right px-4 py-3">Max Leverage</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($instruments as $i): ?>
                <tr>
                    <td class="px-4 py-3 font-medium"><?= e($i['symbol']) ?> <span class="text-slate-500 text-xs"><?= e($i['display_name']) ?></span></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($i['class_label']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e($i['current_price']) ?></td>
                    <td class="px-4 py-3 text-right <?= $i['daily_change_percent'] >= 0 ? 'badge-gain' : 'badge-loss' ?>"><?= e((string) $i['daily_change_percent']) ?>%</td>
                    <td class="px-4 py-3 text-right"><?= e((string) $i['leverage_max']) ?>x</td>
                    <td class="px-4 py-3 text-right"><a href="/dashboard/forex/trade/<?= e($i['symbol']) ?>" class="btn-secondary !py-1 !px-3">Trade</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
