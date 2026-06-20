<?php $this->layout('layouts/dashboard-forex', ['title' => 'Watchlist']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Watchlist</h1>

<h2 class="text-lg font-semibold text-white mb-3">Watching</h2>
<div class="card !p-0 overflow-hidden mb-8">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr><th class="text-left px-4 py-3">Symbol</th><th class="text-right px-4 py-3">Price</th><th class="text-right px-4 py-3">24h</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($watched as $i): ?>
                <tr>
                    <td class="px-4 py-3 font-medium"><?= e($i['symbol']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e($i['current_price']) ?></td>
                    <td class="px-4 py-3 text-right <?= $i['daily_change_percent'] >= 0 ? 'badge-gain' : 'badge-loss' ?>"><?= e((string) $i['daily_change_percent']) ?>%</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="/dashboard/forex/watchlist/remove">
                            <?= csrf_field() ?>
                            <input type="hidden" name="instrument_id" value="<?= e((string) $i['id']) ?>">
                            <button type="submit" class="btn-secondary !py-1 !px-3">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($watched === []): ?>
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Your watchlist is empty.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 class="text-lg font-semibold text-white mb-3">Add Instruments</h2>
<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($instruments as $i): ?>
                <tr>
                    <td class="px-4 py-3 font-medium"><?= e($i['symbol']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e($i['current_price']) ?></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="/dashboard/forex/watchlist/add">
                            <?= csrf_field() ?>
                            <input type="hidden" name="instrument_id" value="<?= e((string) $i['id']) ?>">
                            <button type="submit" class="btn-secondary !py-1 !px-3">Add</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
