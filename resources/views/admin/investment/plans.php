<?php $this->layout('layouts/admin', ['title' => 'Investment Plans']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Investment Plans</h1>

<div class="card mb-8">
    <h2 class="text-lg font-semibold text-white mb-4">Create Plan</h2>
    <form method="POST" action="/admin/investment/plans" class="grid sm:grid-cols-3 gap-3">
        <?= csrf_field() ?>
        <input type="text" name="name" placeholder="Name" required class="input">
        <input type="text" name="slug" placeholder="Slug" required class="input">
        <select name="tier" class="select">
            <option value="starter">Starter</option>
            <option value="growth">Growth</option>
            <option value="premium">Premium</option>
            <option value="elite">Elite</option>
        </select>
        <input type="text" name="min_amount" placeholder="Min Amount" required class="input">
        <input type="text" name="max_amount" placeholder="Max Amount (optional)" class="input">
        <input type="text" name="roi_percent" placeholder="ROI %" required class="input">
        <select name="roi_period" class="select">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </select>
        <input type="number" name="duration_days" placeholder="Duration (days)" required class="input">
        <input type="number" name="sort_order" placeholder="Sort Order" value="0" class="input">
        <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="compounding_allowed" value="1"> Compounding allowed</label>
        <textarea name="description" placeholder="Description" class="input sm:col-span-3"></textarea>
        <button type="submit" class="btn-primary sm:col-span-3">Create Plan</button>
    </form>
</div>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">Name</th>
                <th class="py-2 pr-4">Tier</th>
                <th class="py-2 pr-4">Min/Max</th>
                <th class="py-2 pr-4">ROI</th>
                <th class="py-2 pr-4">Duration</th>
                <th class="py-2 pr-4">Active</th>
                <th class="py-2 pr-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $plan): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($plan['name']) ?></td>
                    <td class="py-2 pr-4"><?= e($plan['tier']) ?></td>
                    <td class="py-2 pr-4"><?= e(money($plan['min_amount'])) ?> / <?= e($plan['max_amount'] !== null ? money($plan['max_amount']) : '—') ?></td>
                    <td class="py-2 pr-4"><?= e($plan['roi_percent']) ?>% / <?= e($plan['roi_period']) ?></td>
                    <td class="py-2 pr-4"><?= e((string) $plan['duration_days']) ?>d</td>
                    <td class="py-2 pr-4"><?= ((int) $plan['is_active']) === 1 ? 'Yes' : 'No' ?></td>
                    <td class="py-2 pr-4 flex gap-1">
                        <form method="POST" action="/admin/investment/plans/toggle" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e((string) $plan['id']) ?>">
                            <button type="submit" class="btn-secondary !py-1 !px-2 text-xs">Toggle Active</button>
                        </form>
                        <details class="inline-block">
                            <summary class="btn-secondary !py-1 !px-2 text-xs cursor-pointer list-none">Edit</summary>
                            <form method="POST" action="/admin/investment/plans/update" class="absolute z-10 mt-2 grid gap-2 card w-72">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e((string) $plan['id']) ?>">
                                <input type="text" name="name" value="<?= e($plan['name']) ?>" required class="input">
                                <select name="tier" class="select">
                                    <?php foreach (['starter', 'growth', 'premium', 'elite'] as $tier): ?>
                                        <option value="<?= e($tier) ?>" <?= $plan['tier'] === $tier ? 'selected' : '' ?>><?= e(ucfirst($tier)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="min_amount" value="<?= e($plan['min_amount']) ?>" required class="input">
                                <input type="text" name="max_amount" value="<?= e($plan['max_amount'] ?? '') ?>" class="input">
                                <input type="text" name="roi_percent" value="<?= e($plan['roi_percent']) ?>" required class="input">
                                <select name="roi_period" class="select">
                                    <?php foreach (['daily', 'weekly', 'monthly'] as $period): ?>
                                        <option value="<?= e($period) ?>" <?= $plan['roi_period'] === $period ? 'selected' : '' ?>><?= e(ucfirst($period)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="duration_days" value="<?= e((string) $plan['duration_days']) ?>" required class="input">
                                <input type="number" name="sort_order" value="<?= e((string) $plan['sort_order']) ?>" class="input">
                                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="compounding_allowed" value="1" <?= ((int) $plan['compounding_allowed']) === 1 ? 'checked' : '' ?>> Compounding</label>
                                <textarea name="description" class="input"><?= e($plan['description'] ?? '') ?></textarea>
                                <button type="submit" class="btn-primary">Save</button>
                            </form>
                        </details>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
