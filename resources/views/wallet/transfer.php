<?php $this->layout('layouts/app', ['title' => 'Transfer Funds']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Transfer Between Sub-Accounts</h1>

<?= $this->insert('partials/wallet-balances', ['balances' => $balances]) ?>

<div class="max-w-md card">
    <form method="POST" action="/wallet/transfer" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm text-slate-300 mb-1">From</label>
            <select name="from" required class="select">
                <option value="main">Main</option>
                <option value="investment">Investment</option>
                <option value="forex">Forex &amp; Trading</option>
                <option value="realestate">Real Estate</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">To</label>
            <select name="to" required class="select">
                <option value="investment">Investment</option>
                <option value="main">Main</option>
                <option value="forex">Forex &amp; Trading</option>
                <option value="realestate">Real Estate</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Amount</label>
            <input type="text" name="amount" inputmode="decimal" required class="input" placeholder="0.00">
        </div>
        <button type="submit" class="btn-primary w-full">Transfer</button>
    </form>
</div>

<?php $this->stop() ?>
