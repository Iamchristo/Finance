<?php $this->layout('layouts/app', ['title' => 'Simulated Platform Disclosure']) ?>
<?php $this->start('body') ?>

<article class="card space-y-4 text-slate-300">
    <h1 class="text-3xl font-bold text-white mb-2">Simulated Platform Disclosure</h1>
    <p class="text-amber-300 text-sm font-medium"><?= e($simulatedBanner) ?></p>

    <p>Meridian Capital is a demonstration product. Every balance, price, trade, position, property valuation, and payout shown anywhere on this site or in the dashboards is generated for illustration purposes only.</p>

    <h2 class="text-xl font-bold text-white pt-4">No real funds</h2>
    <p>No deposits are accepted, no funds are custodied, and no withdrawals are processed. Wallet balances are figures stored in a database and have no monetary value.</p>

    <h2 class="text-xl font-bold text-white pt-4">No real markets</h2>
    <p>Forex, crypto, indices, and commodities prices are produced by an internal random-walk simulator. They are not sourced from, and do not represent, real exchanges or market data providers.</p>

    <h2 class="text-xl font-bold text-white pt-4">No investment advice</h2>
    <p>Nothing on this platform — including plan descriptions, strategy listings, property listings, calculators, or blog content — constitutes financial, investment, or legal advice.</p>

    <h2 class="text-xl font-bold text-white pt-4">Purpose</h2>
    <p>This platform exists solely to demonstrate product design and engineering for a hypothetical multi-vertical fintech offering.</p>
</article>

<?php $this->stop() ?>
