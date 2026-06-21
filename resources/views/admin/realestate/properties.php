<?php $this->layout('layouts/admin', ['title' => 'Properties']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Real Estate Properties</h1>

<div class="card mb-8">
    <h2 class="text-lg font-semibold text-white mb-4">Add Property</h2>
    <form method="POST" action="/admin/realestate/properties" class="grid sm:grid-cols-3 gap-3">
        <?= csrf_field() ?>
        <input type="text" name="title" placeholder="Title" required class="input">
        <input type="text" name="slug" placeholder="Slug" required class="input">
        <select name="property_type" class="select">
            <option value="residential">Residential</option>
            <option value="commercial">Commercial</option>
            <option value="mixed_use">Mixed Use</option>
            <option value="land">Land</option>
        </select>
        <input type="text" name="address" placeholder="Address" class="input">
        <input type="text" name="city" placeholder="City" class="input">
        <input type="text" name="country" placeholder="Country" class="input">
        <input type="text" name="total_value" placeholder="Total Value" required class="input">
        <input type="number" name="total_shares" placeholder="Total Shares" required class="input">
        <input type="text" name="share_price" placeholder="Share Price" required class="input">
        <input type="text" name="expected_annual_roi_percent" placeholder="Expected Annual ROI %" required class="input">
        <select name="mode" class="select">
            <option value="fractional">Fractional</option>
            <option value="marketplace">Marketplace</option>
        </select>
        <input type="text" name="cover_image_path" placeholder="Cover Image Path" class="input">
        <textarea name="description" placeholder="Description" class="input sm:col-span-3"></textarea>
        <button type="submit" class="btn-primary sm:col-span-3">Add Property</button>
    </form>
</div>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">Title</th>
                <th class="py-2 pr-4">Type</th>
                <th class="py-2 pr-4">Funding</th>
                <th class="py-2 pr-4">Shares</th>
                <th class="py-2 pr-4">Active</th>
                <th class="py-2 pr-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($properties as $property): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($property['title']) ?></td>
                    <td class="py-2 pr-4"><?= e($property['property_type']) ?></td>
                    <td class="py-2 pr-4"><?= e($property['funding_status']) ?></td>
                    <td class="py-2 pr-4"><?= e((string) $property['shares_sold']) ?>/<?= e((string) $property['total_shares']) ?></td>
                    <td class="py-2 pr-4"><?= ((int) $property['is_active']) === 1 ? 'Yes' : 'No' ?></td>
                    <td class="py-2 pr-4">
                        <form method="POST" action="/admin/realestate/properties/toggle" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e((string) $property['id']) ?>">
                            <button type="submit" class="btn-secondary !py-1 !px-2 text-xs">Toggle Active</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
