<?php $this->layout('layouts/admin', ['title' => 'Admin Dashboard']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Platform Overview</h1>

<div class="grid sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Total Users</div><div class="text-2xl font-bold text-white"><?= e((string) $stats['total_users']) ?></div></div>
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Pending KYC</div><div class="text-2xl font-bold text-white"><?= e((string) $stats['pending_kyc']) ?></div></div>
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Pending Listings</div><div class="text-2xl font-bold text-white"><?= e((string) $stats['pending_listings']) ?></div></div>
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Active Subscriptions</div><div class="text-2xl font-bold text-white"><?= e((string) $stats['active_subscriptions']) ?></div></div>
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Open FX Positions</div><div class="text-2xl font-bold text-white"><?= e((string) $stats['open_positions']) ?></div></div>
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Active Property Investments</div><div class="text-2xl font-bold text-white"><?= e((string) $stats['active_property_investments']) ?></div></div>
</div>

<h2 class="text-lg font-semibold text-white mb-3">Total Simulated Balances</h2>
<div class="grid sm:grid-cols-4 gap-4">
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Main</div><div class="text-xl font-bold text-white"><?= e(money($stats['total_main_balance'])) ?></div></div>
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Investment</div><div class="text-xl font-bold text-white"><?= e(money($stats['total_investment_balance'])) ?></div></div>
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Forex</div><div class="text-xl font-bold text-white"><?= e(money($stats['total_forex_balance'])) ?></div></div>
    <div class="card"><div class="text-slate-400 text-xs uppercase mb-1">Real Estate</div><div class="text-xl font-bold text-white"><?= e(money($stats['total_realestate_balance'])) ?></div></div>
</div>

<?php $this->stop() ?>
