<?php $this->layout('layouts/admin', ['title' => 'KYC Review']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">KYC Review Queue</h1>

<h2 class="text-lg font-semibold text-white mb-3">Pending (<?= e((string) count($pending)) ?>)</h2>
<div class="card overflow-x-auto mb-8">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">User</th>
                <th class="py-2 pr-4">Document</th>
                <th class="py-2 pr-4">Submitted</th>
                <th class="py-2 pr-4">Decision</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending as $submission): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($submission['first_name'] . ' ' . $submission['last_name']) ?> <span class="text-slate-500">(<?= e($submission['email']) ?>)</span></td>
                    <td class="py-2 pr-4"><?= e($submission['document_type']) ?></td>
                    <td class="py-2 pr-4"><?= e($submission['submitted_at']) ?></td>
                    <td class="py-2 pr-4">
                        <div class="flex gap-2">
                            <form method="POST" action="/admin/kyc/approve">
                                <?= csrf_field() ?>
                                <input type="hidden" name="submission_id" value="<?= e((string) $submission['id']) ?>">
                                <button type="submit" class="btn-primary !py-1 !px-3 text-xs">Approve</button>
                            </form>
                            <form method="POST" action="/admin/kyc/reject" class="flex gap-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="submission_id" value="<?= e((string) $submission['id']) ?>">
                                <input type="text" name="reason" placeholder="Reason" required class="input !py-1 !w-40">
                                <button type="submit" class="btn-secondary !py-1 !px-3 text-xs">Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($pending === []): ?>
                <tr><td colspan="4" class="py-4 text-slate-500">No pending submissions.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 class="text-lg font-semibold text-white mb-3">All Submissions</h2>
<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">User</th>
                <th class="py-2 pr-4">Document</th>
                <th class="py-2 pr-4">Status</th>
                <th class="py-2 pr-4">Reviewed</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all as $submission): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($submission['first_name'] . ' ' . $submission['last_name']) ?></td>
                    <td class="py-2 pr-4"><?= e($submission['document_type']) ?></td>
                    <td class="py-2 pr-4"><?= e($submission['status']) ?></td>
                    <td class="py-2 pr-4"><?= e($submission['reviewed_at'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
