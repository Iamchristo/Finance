<?php $this->layout('layouts/admin', ['title' => 'Audit Log']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Audit Log</h1>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">Actor</th>
                <th class="py-2 pr-4">Action</th>
                <th class="py-2 pr-4">Subject</th>
                <th class="py-2 pr-4">Metadata</th>
                <th class="py-2 pr-4">IP</th>
                <th class="py-2 pr-4">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($entry['actor_name'] ?? 'System') ?> <span class="text-slate-500">(<?= e($entry['actor_type']) ?>)</span></td>
                    <td class="py-2 pr-4"><?= e($entry['action']) ?></td>
                    <td class="py-2 pr-4"><?= e(($entry['subject_type'] ?? '') . ' #' . ($entry['subject_id'] ?? '')) ?></td>
                    <td class="py-2 pr-4 text-slate-400 max-w-xs truncate" title="<?= e($entry['metadata'] ?? '') ?>"><?= e($entry['metadata'] ?? '') ?></td>
                    <td class="py-2 pr-4"><?= e($entry['ip_address'] ?? '') ?></td>
                    <td class="py-2 pr-4"><?= e($entry['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($entries === []): ?>
                <tr><td colspan="6" class="py-4 text-slate-500">No audit entries found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="flex gap-2 mt-4 text-sm">
    <?php if ($page > 1): ?>
        <a href="/admin/audit?page=<?= e((string) ($page - 1)) ?>" class="btn-secondary !py-1 !px-3">Previous</a>
    <?php endif; ?>
    <?php if ($page * $perPage < $totalCount): ?>
        <a href="/admin/audit?page=<?= e((string) ($page + 1)) ?>" class="btn-secondary !py-1 !px-3">Next</a>
    <?php endif; ?>
</div>

<?php $this->stop() ?>
