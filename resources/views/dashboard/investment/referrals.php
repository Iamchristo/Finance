<?php $this->layout('layouts/dashboard-investment', ['title' => 'Referrals']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Referrals</h1>

<div class="card mb-6">
    <p class="text-sm text-slate-400 mb-2">Share your referral code to earn commissions when others subscribe.</p>
    <div class="flex items-center gap-3">
        <code class="px-3 py-2 rounded-lg bg-surface-200 text-emerald-400 font-mono"><?= e($referralCode) ?></code>
    </div>
</div>

<h2 class="text-lg font-semibold text-white mb-3">Referred Users</h2>
<div class="card !p-0 overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr><th class="text-left px-4 py-3">Name</th><th class="text-left px-4 py-3">Email</th><th class="text-left px-4 py-3">Joined</th></tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($referredUsers as $u): ?>
                <tr>
                    <td class="px-4 py-3"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($u['email']) ?></td>
                    <td class="px-4 py-3 text-slate-400"><?= e($u['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($referredUsers === []): ?>
                <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">No referrals yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 class="text-lg font-semibold text-white mb-3">Commissions Earned</h2>
<div class="card !p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-surface-200 text-slate-400 text-xs uppercase">
            <tr><th class="text-left px-4 py-3">From</th><th class="text-right px-4 py-3">Amount</th><th class="text-right px-4 py-3">Rate</th><th class="text-left px-4 py-3">Date</th></tr>
        </thead>
        <tbody class="divide-y divide-surface-200">
            <?php foreach ($commissions as $c): ?>
                <tr>
                    <td class="px-4 py-3"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td>
                    <td class="px-4 py-3 text-right badge-gain">+<?= e(money($c['amount'])) ?></td>
                    <td class="px-4 py-3 text-right text-slate-400"><?= e($c['commission_rate']) ?>%</td>
                    <td class="px-4 py-3 text-slate-400"><?= e($c['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($commissions === []): ?>
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No commissions earned yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
