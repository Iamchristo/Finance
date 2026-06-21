<?php $this->layout('layouts/app', ['title' => 'Real Estate']) ?>
<?php $this->start('body') ?>

<section class="text-center py-10">
    <div class="text-violet-400 text-sm font-semibold mb-2">Real Estate</div>
    <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">Fractional ownership &amp; peer-to-peer marketplace</h1>
    <p class="text-slate-400 max-w-2xl mx-auto"><?= e($simulatedBanner) ?></p>
</section>

<section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 py-6">
    <?php foreach ($properties as $property): ?>
        <a href="/real-estate/<?= e($property['slug']) ?>" class="card hover:shadow-elevated transition block">
            <div class="text-xs font-semibold uppercase text-violet-400 mb-2"><?= e($property['property_type']) ?> &middot; <?= e($property['mode']) ?></div>
            <h2 class="text-lg font-bold text-white mb-1"><?= e($property['title']) ?></h2>
            <div class="text-xs text-slate-500 mb-3"><?= e($property['city'] ?? '') ?><?= ($property['city'] && $property['country']) ? ', ' : '' ?><?= e($property['country'] ?? '') ?></div>
            <p class="text-sm text-slate-400 mb-4 line-clamp-3"><?= e($property['description'] ?? '') ?></p>
            <div class="flex justify-between items-end">
                <div>
                    <div class="text-lg font-bold text-white"><?= e(money($property['total_value'])) ?></div>
                    <div class="text-xs text-slate-500"><?= e((string) $property['shares_sold']) ?>/<?= e((string) $property['total_shares']) ?> shares sold</div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-gain-500"><?= e($property['expected_annual_roi_percent']) ?>%</div>
                    <div class="text-xs text-slate-500">annual ROI</div>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
    <?php if ($properties === []): ?>
        <p class="text-slate-500 col-span-full text-center py-8">No properties are currently available.</p>
    <?php endif; ?>
</section>

<section class="card mt-4 text-center">
    <h2 class="text-xl font-bold text-white mb-2">Own a piece, or list your own</h2>
    <p class="text-sm text-slate-400 mb-4">Buy fractional shares of pooled properties, or list a simulated holding for sale on the marketplace.</p>
    <a href="/register" class="btn-primary">Get Started</a>
</section>

<?php $this->stop() ?>
