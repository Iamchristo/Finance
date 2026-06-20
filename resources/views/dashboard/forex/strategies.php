<?php $this->layout('layouts/dashboard-forex', ['title' => 'Strategies']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Strategies</h1>

<div class="grid sm:grid-cols-2 gap-4 mb-8">
    <?php foreach ($strategies as $s): ?>
        <div class="card">
            <div class="flex justify-between items-baseline mb-2">
                <h2 class="text-lg font-semibold text-white"><?= e($s['name']) ?></h2>
                <span class="text-xs uppercase font-semibold text-slate-400"><?= e($s['risk_level']) ?> risk</span>
            </div>
            <p class="text-sm text-slate-400 mb-3"><?= e($s['description'] ?? '') ?></p>
            <p class="text-sm mb-4">Simulated monthly return: <span class="badge-gain font-semibold"><?= e($s['simulated_monthly_return_percent']) ?>%</span></p>
            <form method="POST" action="/dashboard/forex/strategies/subscribe" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="strategy_id" value="<?= e((string) $s['id']) ?>">
                <input type="text" name="amount" inputmode="decimal" required class="input" placeholder="Allocate amount">
                <button type="submit" class="btn-primary whitespace-nowrap">Allocate</button>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if ($strategies === []): ?>
        <p class="text-slate-500">No strategies are currently available.</p>
    <?php endif; ?>
</div>

<h2 class="text-lg font-semibold text-white mb-3">My Allocations</h2>
<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr><th class="text-left px-4 py-3">Strategy</th><th class="text-right px-4 py-3">Allocated</th><th class="text-left px-4 py-3">Status</th><th class="text-left px-4 py-3">Started</th></tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td class="px-4 py-3"><?= e($sub['name']) ?></td>
                    <td class="px-4 py-3 text-right"><?= e(money($sub['allocated_amount'])) ?></td>
                    <td class="px-4 py-3 capitalize"><?= e($sub['status']) ?></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($sub['started_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($subscriptions === []): ?>
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No active allocations.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
