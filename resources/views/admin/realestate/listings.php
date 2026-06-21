<?php $this->layout('layouts/admin', ['title' => 'Listing Moderation']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Marketplace Listing Moderation</h1>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">Title</th>
                <th class="py-2 pr-4">Seller</th>
                <th class="py-2 pr-4">Asking Price</th>
                <th class="py-2 pr-4">Status</th>
                <th class="py-2 pr-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listings as $listing): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($listing['title']) ?></td>
                    <td class="py-2 pr-4"><?= e($listing['seller_name']) ?></td>
                    <td class="py-2 pr-4"><?= e(money($listing['asking_price'])) ?></td>
                    <td class="py-2 pr-4"><?= e($listing['status']) ?></td>
                    <td class="py-2 pr-4">
                        <?php if ($listing['status'] === 'pending_review'): ?>
                            <div class="flex gap-1">
                                <form method="POST" action="/admin/realestate/listings/review">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= e((string) $listing['id']) ?>">
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn-primary !py-1 !px-3 text-xs">Approve</button>
                                </form>
                                <form method="POST" action="/admin/realestate/listings/review">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= e((string) $listing['id']) ?>">
                                    <input type="hidden" name="status" value="withdrawn">
                                    <button type="submit" class="btn-secondary !py-1 !px-3 text-xs">Reject</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <span class="text-slate-500">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($listings === []): ?>
                <tr><td colspan="5" class="py-4 text-slate-500">No listings found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
