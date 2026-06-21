<?php $this->layout('layouts/app', ['title' => 'Trading Strategies']) ?>
<?php $this->start('body') ?>

<a href="/trading" class="text-sm text-slate-400 hover:text-white">&larr; Trading</a>

<section class="text-center py-10">
    <h1 class="text-3xl font-bold text-white tracking-tight mb-4">Curated trading strategies</h1>
    <p class="text-slate-400 max-w-2xl mx-auto">Allocate a simulated amount to a strategy and let it run against its target risk profile and monthly return.</p>
</section>

<section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($strategies as $strategy): ?>
        <div class="card">
            <div class="text-xs font-semibold uppercase mb-2 <?= match ($strategy['risk_level']) { 'low' => 'text-gain-500', 'high' => 'text-loss-500', default => 'text-amber-400' } ?>">
                <?= e($strategy['risk_level']) ?> risk
            </div>
            <h2 class="text-lg font-bold text-white mb-2"><?= e($strategy['name']) ?></h2>
            <p class="text-sm text-slate-400 mb-4"><?= e($strategy['description'] ?? '') ?></p>
            <div class="text-2xl font-bold text-gain-500"><?= e($strategy['simulated_monthly_return_percent']) ?>%</div>
            <div class="text-xs text-slate-500">simulated monthly return</div>
        </div>
    <?php endforeach; ?>
    <?php if ($strategies === []): ?>
        <p class="text-slate-500 col-span-full text-center py-8">No strategies are currently available.</p>
    <?php endif; ?>
</section>

<?php $this->stop() ?>
