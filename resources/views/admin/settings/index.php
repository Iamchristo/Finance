<?php $this->layout('layouts/admin', ['title' => 'Platform Settings']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Platform Settings</h1>

<div class="card mb-8 max-w-xl">
    <h2 class="text-lg font-semibold text-white mb-4">Add / Update Setting</h2>
    <form method="POST" action="/admin/settings" class="space-y-3">
        <?= csrf_field() ?>
        <input type="text" name="setting_key" placeholder="Setting key" required class="input">
        <input type="text" name="setting_value" placeholder="Value" class="input">
        <select name="setting_type" class="select">
            <option value="string">String</option>
            <option value="int">Integer</option>
            <option value="bool">Boolean</option>
            <option value="json">JSON</option>
        </select>
        <button type="submit" class="btn-primary">Save Setting</button>
    </form>
</div>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">Key</th>
                <th class="py-2 pr-4">Value</th>
                <th class="py-2 pr-4">Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($settings as $setting): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($setting['setting_key']) ?></td>
                    <td class="py-2 pr-4"><?= e($setting['setting_value'] ?? '') ?></td>
                    <td class="py-2 pr-4"><?= e($setting['setting_type']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($settings === []): ?>
                <tr><td colspan="3" class="py-4 text-slate-500">No settings configured.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
