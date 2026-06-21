<?php $this->layout('layouts/app', ['title' => 'Trading']) ?>
<?php $this->start('body') ?>

<section class="text-center py-10">
    <div class="text-sky-400 text-sm font-semibold mb-2">Trading</div>
    <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">Forex, crypto, indices &amp; commodities</h1>
    <p class="text-slate-400 max-w-2xl mx-auto"><?= e($simulatedBanner) ?></p>
    <div class="mt-4">
        <a href="/trading/strategies" class="text-sm text-brand-400 hover:text-brand-300">Browse curated strategies &rarr;</a>
    </div>
</section>

<?php foreach ($byClass as $className => $instruments): ?>
    <section class="py-6">
        <h2 class="text-lg font-bold text-white mb-4 capitalize"><?= e($instruments[0]['class_label']) ?></h2>
        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 border-b border-surface-300">
                        <th class="py-2 pr-4">Symbol</th>
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Price</th>
                        <th class="py-2 pr-4">24h Change</th>
                        <th class="py-2 pr-4">Max Leverage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($instruments as $instrument): ?>
                        <tr class="border-b border-surface-200">
                            <td class="py-2 pr-4 text-white font-medium">
                                <a href="/trading/<?= e($instrument['symbol']) ?>" class="hover:text-brand-400"><?= e($instrument['symbol']) ?></a>
                            </td>
                            <td class="py-2 pr-4"><?= e($instrument['display_name']) ?></td>
                            <td class="py-2 pr-4"><?= e(money($instrument['current_price'])) ?></td>
                            <td class="py-2 pr-4 <?= ((float) $instrument['daily_change_percent']) >= 0 ? 'badge-gain' : 'badge-loss' ?>">
                                <?= e((string) $instrument['daily_change_percent']) ?>%
                            </td>
                            <td class="py-2 pr-4"><?= e((string) $instrument['leverage_max']) ?>x</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endforeach; ?>
<?php if ($byClass === []): ?>
    <p class="text-slate-500 text-center py-8">No instruments are currently available.</p>
<?php endif; ?>

<section class="card mt-4 text-center">
    <h2 class="text-xl font-bold text-white mb-2">Start trading</h2>
    <p class="text-sm text-slate-400 mb-4">Create an account, fund your Forex balance, and place your first simulated order.</p>
    <a href="/register" class="btn-primary">Get Started</a>
</section>

<?php $this->stop() ?>
