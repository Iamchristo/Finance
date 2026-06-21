<?php $this->layout('layouts/app', ['title' => 'Risk Disclosure']) ?>
<?php $this->start('body') ?>

<article class="card space-y-4 text-slate-300">
    <h1 class="text-3xl font-bold text-white mb-2">Risk Disclosure</h1>
    <p class="text-amber-300 text-sm font-medium"><?= e($simulatedBanner) ?></p>

    <p>This page describes, in general terms, the categories of risk associated with the kinds of products this platform simulates — investment plans, leveraged trading, and fractional/marketplace real estate — for educational illustration only. Because the platform is simulated, none of these risks materialize with real funds here.</p>

    <h2 class="text-xl font-bold text-white pt-4">Investment plan risk</h2>
    <p>In a real-world equivalent, fixed or tiered ROI products can carry principal risk, counterparty risk, and liquidity risk, particularly where returns are not backed by transparent, audited underlying activity.</p>

    <h2 class="text-xl font-bold text-white pt-4">Leveraged trading risk</h2>
    <p>Leverage amplifies both gains and losses. In real margin trading, adverse price movements can exceed posted margin, leading to margin calls or forced liquidation. Volatility in forex, crypto, indices, and commodities markets can be substantial and rapid.</p>

    <h2 class="text-xl font-bold text-white pt-4">Real estate risk</h2>
    <p>Real fractional real estate investments are typically illiquid, valuations can be subjective or stale, and pooled structures can carry concentration risk in a single asset or market.</p>

    <h2 class="text-xl font-bold text-white pt-4">This platform</h2>
    <p>Because all balances, prices, and payouts here are simulated, none of the above risks apply to your activity on this platform — but they reflect the real-world risk categories the simulated experience is modeled on.</p>
</article>

<?php $this->stop() ?>
