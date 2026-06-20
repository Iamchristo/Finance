<?php $this->layout('layouts/app', ['title' => 'Invest. Trade. Own.']) ?>
<?php $this->start('body') ?>

<section class="text-center py-16">
    <h1 class="text-4xl sm:text-5xl font-bold text-white tracking-tight mb-4">
        One account. Three ways to grow.
    </h1>
    <p class="text-lg text-slate-400 max-w-2xl mx-auto mb-8">
        High-yield investment plans, forex/crypto/indices/commodities trading, and real estate —
        all sharing a single wallet you control. <?= e($simulatedBanner) ?>
    </p>
    <div class="flex justify-center gap-4">
        <a href="/register" class="btn-primary">Get Started</a>
        <a href="/login" class="btn-secondary">Sign In</a>
    </div>
</section>

<section class="grid sm:grid-cols-3 gap-6 py-8">
    <div class="card">
        <div class="text-amber-400 text-sm font-semibold mb-2">High-Yield Investment</div>
        <h2 class="text-xl font-bold text-white mb-2">Tiered investment plans</h2>
        <p class="text-sm text-slate-400">Subscribe to ROI-bearing plans with daily, weekly, or monthly accrual and a built-in referral program.</p>
    </div>
    <div class="card">
        <div class="text-sky-400 text-sm font-semibold mb-2">Trading</div>
        <h2 class="text-xl font-bold text-white mb-2">Forex, crypto, indices &amp; commodities</h2>
        <p class="text-sm text-slate-400">Live candlestick charts, leveraged positions, watchlists, and curated strategies — all simulated.</p>
    </div>
    <div class="card">
        <div class="text-violet-400 text-sm font-semibold mb-2">Real Estate</div>
        <h2 class="text-xl font-bold text-white mb-2">Fractional ownership &amp; marketplace</h2>
        <p class="text-sm text-slate-400">Invest in pooled properties or list and sell your own on our peer-to-peer marketplace.</p>
    </div>
</section>

<?php $this->stop() ?>
