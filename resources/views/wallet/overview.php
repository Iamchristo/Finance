<?php $this->layout('layouts/app', ['title' => 'Wallet']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Your Wallet</h1>

<?= $this->insert('partials/wallet-balances', ['balances' => $balances]) ?>

<div class="flex gap-3">
    <a href="/wallet/transfer" class="btn-primary">Transfer Between Sub-Accounts</a>
    <a href="/wallet/ledger" class="btn-secondary">View Ledger</a>
</div>

<div class="grid sm:grid-cols-3 gap-4 mt-8">
    <a href="/dashboard/investment" class="card hover:bg-surface-200 transition">
        <div class="text-amber-400 text-sm font-semibold mb-1">Investment</div>
        <p class="text-sm text-slate-400">High-yield plans and ROI accrual.</p>
    </a>
    <a href="/dashboard/forex" class="card hover:bg-surface-200 transition">
        <div class="text-sky-400 text-sm font-semibold mb-1">Trading</div>
        <p class="text-sm text-slate-400">Forex, crypto, indices & commodities.</p>
    </a>
    <a href="/dashboard/realestate" class="card hover:bg-surface-200 transition">
        <div class="text-violet-400 text-sm font-semibold mb-1">Real Estate</div>
        <p class="text-sm text-slate-400">Fractional ownership & marketplace.</p>
    </a>
</div>

<?php $this->stop() ?>
