<?php $this->layout('layouts/dashboard-realestate', ['title' => 'My Listings']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">My Listings</h1>

<div class="card mb-8">
    <h2 class="text-lg font-semibold text-white mb-3">List a Property</h2>
    <form method="POST" action="/dashboard/realestate/listings" class="space-y-3">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Title</label>
            <input type="text" name="title" required class="input">
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Description</label>
            <textarea name="description" rows="3" class="input"></textarea>
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Asking Price</label>
            <input type="text" name="asking_price" inputmode="decimal" required class="input">
        </div>
        <button type="submit" class="btn-primary">Submit for Review</button>
        <p class="text-xs text-slate-500">Listings are reviewed by our team before going live on the marketplace.</p>
    </form>
</div>

<?php foreach ($listings as $listing): ?>
    <div class="card mb-4">
        <div class="flex justify-between items-baseline mb-2">
            <h3 class="text-lg font-semibold text-white"><?= e($listing['title']) ?></h3>
            <span class="text-xs uppercase font-semibold text-slate-400"><?= e($listing['status']) ?></span>
        </div>
        <p class="text-sm text-slate-400 mb-3"><?= e(money($listing['asking_price'])) ?></p>

        <?php if ($listing['offers'] !== []): ?>
            <table class="w-full text-sm">
                <thead class="text-slate-400 text-xs uppercase">
                    <tr><th class="text-left py-2">Buyer</th><th class="text-right py-2">Offer</th><th class="text-left py-2">Status</th><th class="py-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-surface-200">
                    <?php foreach ($listing['offers'] as $offer): ?>
                        <tr>
                            <td class="py-2"><?= e($offer['buyer_name']) ?></td>
                            <td class="py-2 text-right"><?= e(money($offer['offer_amount'])) ?></td>
                            <td class="py-2 capitalize"><?= e($offer['status']) ?></td>
                            <td class="py-2 text-right">
                                <?php if ($offer['status'] === 'pending'): ?>
                                    <form method="POST" action="/dashboard/realestate/listings/offers/accept" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="offer_id" value="<?= e((string) $offer['id']) ?>">
                                        <button type="submit" class="btn-primary !py-1 !px-3">Accept</button>
                                    </form>
                                    <form method="POST" action="/dashboard/realestate/listings/offers/reject" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="offer_id" value="<?= e((string) $offer['id']) ?>">
                                        <button type="submit" class="btn-secondary !py-1 !px-3">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-sm text-slate-500">No offers yet.</p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php if ($listings === []): ?>
    <p class="text-slate-500">You haven't created any listings yet.</p>
<?php endif; ?>

<?php $this->stop() ?>
