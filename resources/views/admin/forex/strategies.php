<?php $this->layout('layouts/admin', ['title' => 'Forex Strategies']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Copy-Trade Strategies</h1>

<div class="card mb-8">
    <h2 class="text-lg font-semibold text-white mb-4">Add Strategy</h2>
    <form method="POST" action="/admin/forex/strategies" class="grid sm:grid-cols-3 gap-3">
        <?= csrf_field() ?>
        <input type="text" name="name" placeholder="Name" required class="input">
        <input type="text" name="slug" placeholder="Slug" required class="input">
        <select name="risk_level" class="select">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
        <input type="text" name="simulated_monthly_return_percent" placeholder="Simulated Monthly Return %" required class="input">
        <textarea name="description" placeholder="Description" class="input sm:col-span-3"></textarea>
        <button type="submit" class="btn-primary sm:col-span-3">Add Strategy</button>
    </form>
</div>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">Name</th>
                <th class="py-2 pr-4">Risk</th>
                <th class="py-2 pr-4">Sim. Monthly Return</th>
                <th class="py-2 pr-4">Active</th>
                <th class="py-2 pr-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($strategies as $strategy): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($strategy['name']) ?></td>
                    <td class="py-2 pr-4"><?= e($strategy['risk_level']) ?></td>
                    <td class="py-2 pr-4"><?= e($strategy['simulated_monthly_return_percent']) ?>%</td>
                    <td class="py-2 pr-4"><?= ((int) $strategy['is_active']) === 1 ? 'Yes' : 'No' ?></td>
                    <td class="py-2 pr-4">
                        <form method="POST" action="/admin/forex/strategies/toggle" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e((string) $strategy['id']) ?>">
                            <button type="submit" class="btn-secondary !py-1 !px-2 text-xs">Toggle Active</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
