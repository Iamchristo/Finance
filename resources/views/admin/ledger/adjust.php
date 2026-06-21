<?php $this->layout('layouts/admin', ['title' => 'Manual Ledger Adjustment']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Manual Ledger Adjustment</h1>

<div class="card max-w-xl">
    <form method="POST" action="/admin/ledger/adjust" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label class="block text-sm text-slate-400 mb-1">User Email</label>
            <input type="email" name="email" required class="input">
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Wallet Section</label>
            <select name="section" required class="select">
                <?php foreach ($sections as $sectionCase): ?>
                    <option value="<?= e($sectionCase->value) ?>"><?= e($sectionCase->label()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Direction</label>
            <select name="direction" required class="select">
                <option value="credit">Credit</option>
                <option value="debit">Debit</option>
            </select>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Amount</label>
            <input type="text" name="amount" inputmode="decimal" required class="input">
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Reason (required, recorded in audit log)</label>
            <input type="text" name="reason" required class="input">
        </div>

        <button type="submit" class="btn-primary">Apply Adjustment</button>
    </form>
</div>

<?php $this->stop() ?>
