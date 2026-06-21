<?php $this->layout('layouts/admin', ['title' => 'Users']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Users</h1>

<form method="GET" action="/admin/users" class="mb-4 flex gap-2">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name or email" class="input max-w-sm">
    <button type="submit" class="btn-secondary">Search</button>
</form>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">Name</th>
                <th class="py-2 pr-4">Email</th>
                <th class="py-2 pr-4">Role</th>
                <th class="py-2 pr-4">Status</th>
                <th class="py-2 pr-4">KYC</th>
                <th class="py-2 pr-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($user->fullName()) ?></td>
                    <td class="py-2 pr-4"><?= e($user->email) ?></td>
                    <td class="py-2 pr-4">
                        <form method="POST" action="/admin/users/role" class="flex items-center gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= e((string) $user->id) ?>">
                            <select name="role_id" class="select !py-1">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= e((string) $role['id']) ?>" <?= (int) $role['id'] === $user->roleId ? 'selected' : '' ?>><?= e($role['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-secondary !py-1 !px-2 text-xs">Save</button>
                        </form>
                    </td>
                    <td class="py-2 pr-4"><?= e($user->status) ?></td>
                    <td class="py-2 pr-4"><?= e($user->kycStatus) ?></td>
                    <td class="py-2 pr-4">
                        <form method="POST" action="/admin/users/status" class="flex gap-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= e((string) $user->id) ?>">
                            <select name="status" class="select !py-1">
                                <option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="suspended" <?= $user->status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                <option value="banned" <?= $user->status === 'banned' ? 'selected' : '' ?>>Banned</option>
                                <option value="pending_verification" <?= $user->status === 'pending_verification' ? 'selected' : '' ?>>Pending</option>
                            </select>
                            <button type="submit" class="btn-secondary !py-1 !px-2 text-xs">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($users === []): ?>
                <tr><td colspan="6" class="py-4 text-slate-500">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="flex gap-2 mt-4 text-sm">
    <?php if ($page > 1): ?>
        <a href="/admin/users?page=<?= e((string) ($page - 1)) ?>&search=<?= e($search) ?>" class="btn-secondary !py-1 !px-3">Previous</a>
    <?php endif; ?>
    <?php if ($page * $perPage < $totalCount): ?>
        <a href="/admin/users?page=<?= e((string) ($page + 1)) ?>&search=<?= e($search) ?>" class="btn-secondary !py-1 !px-3">Next</a>
    <?php endif; ?>
</div>

<?php $this->stop() ?>
