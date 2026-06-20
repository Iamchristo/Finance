<?php $this->layout('layouts/dashboard-realestate', ['title' => 'Marketplace']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Marketplace</h1>

<div class="grid sm:grid-cols-2 gap-4">
    <?php foreach ($listings as $l): ?>
        <div class="card">
            <h2 class="text-lg font-semibold text-white mb-1"><?= e($l['title']) ?></h2>
            <p class="text-sm text-slate-400 mb-3"><?= e($l['description'] ?? '') ?></p>
            <p class="text-sm mb-1 text-slate-400">Seller: <?= e($l['seller_name']) ?></p>
            <p class="text-lg font-semibold text-white mb-4"><?= e(money($l['asking_price'])) ?></p>
            <form method="POST" action="/dashboard/realestate/marketplace/offer" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="listing_id" value="<?= e((string) $l['id']) ?>">
                <input type="text" name="offer_amount" inputmode="decimal" required class="input" placeholder="Your offer">
                <button type="submit" class="btn-primary whitespace-nowrap">Make Offer</button>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if ($listings === []): ?>
        <p class="text-slate-500">No active listings right now.</p>
    <?php endif; ?>
</div>

<?php $this->stop() ?>
