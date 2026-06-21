<?php $this->layout('layouts/admin', ['title' => 'Ledger Explorer']) ?>
<?php $this->start('body') ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Ledger Explorer</h1>
    <a href="/admin/ledger/adjust" class="btn-primary">New Manual Adjustment</a>
</div>

<form method="GET" action="/admin/ledger" class="mb-4 flex gap-2">
    <select name="section" class="select max-w-xs">
        <option value="">All sections</option>
        <?php foreach ($sections as $sectionCase): ?>
            <option value="<?= e($sectionCase->value) ?>" <?= $section === $sectionCase->value ? 'selected' : '' ?>><?= e($sectionCase->label()) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-secondary">Filter</button>
</form>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">User</th>
                <th class="py-2 pr-4">Section</th>
                <th class="py-2 pr-4">Direction</th>
                <th class="py-2 pr-4">Type</th>
                <th class="py-2 pr-4">Amount</th>
                <th class="py-2 pr-4">Balance After</th>
                <th class="py-2 pr-4">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($entry['email']) ?></td>
                    <td class="py-2 pr-4"><?= e($entry['wallet_section']) ?></td>
                    <td class="py-2 pr-4 <?= $entry['direction'] === 'credit' ? 'badge-gain' : 'badge-loss' ?>"><?= e($entry['direction']) ?></td>
                    <td class="py-2 pr-4"><?= e($entry['type']) ?></td>
                    <td class="py-2 pr-4"><?= e(money($entry['amount'])) ?></td>
                    <td class="py-2 pr-4"><?= e(money($entry['balance_after'])) ?></td>
                    <td class="py-2 pr-4"><?= e($entry['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($entries === []): ?>
                <tr><td colspan="7" class="py-4 text-slate-500">No ledger entries found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="flex gap-2 mt-4 text-sm">
    <?php if ($page > 1): ?>
        <a href="/admin/ledger?page=<?= e((string) ($page - 1)) ?>&section=<?= e($section) ?>" class="btn-secondary !py-1 !px-3">Previous</a>
    <?php endif; ?>
    <?php if ($page * $perPage < $totalCount): ?>
        <a href="/admin/ledger?page=<?= e((string) ($page + 1)) ?>&section=<?= e($section) ?>" class="btn-secondary !py-1 !px-3">Next</a>
    <?php endif; ?>
</div>

<?php $this->stop() ?>
