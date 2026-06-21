<?php $this->layout('layouts/admin', ['title' => 'Forex Instruments']) ?>
<?php $this->start('body') ?>

<h1 class="text-2xl font-bold text-white mb-6">Forex / Crypto / Indices / Commodities Instruments</h1>

<div class="card mb-8">
    <h2 class="text-lg font-semibold text-white mb-4">Add Instrument</h2>
    <form method="POST" action="/admin/forex/instruments" class="grid sm:grid-cols-3 gap-3">
        <?= csrf_field() ?>
        <select name="class_id" required class="select">
            <?php foreach ($classes as $class): ?>
                <option value="<?= e((string) $class['id']) ?>"><?= e($class['label']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="symbol" placeholder="Symbol (e.g. EURUSD)" required class="input">
        <input type="text" name="display_name" placeholder="Display Name" required class="input">
        <input type="text" name="base_currency" placeholder="Base Currency" required class="input">
        <input type="text" name="quote_currency" placeholder="Quote Currency" required class="input">
        <input type="text" name="current_price" placeholder="Starting Price" required class="input">
        <input type="number" name="leverage_max" placeholder="Max Leverage" value="1" required class="input">
        <input type="text" name="min_trade_size" placeholder="Min Trade Size" value="0.01" required class="input">
        <input type="number" name="price_precision" placeholder="Price Precision" value="5" required class="input">
        <input type="number" name="sort_order" placeholder="Sort Order" value="0" class="input">
        <button type="submit" class="btn-primary sm:col-span-3">Add Instrument</button>
    </form>
</div>

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-surface-300">
                <th class="py-2 pr-4">Symbol</th>
                <th class="py-2 pr-4">Class</th>
                <th class="py-2 pr-4">Price</th>
                <th class="py-2 pr-4">Leverage</th>
                <th class="py-2 pr-4">Active</th>
                <th class="py-2 pr-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($instruments as $instrument): ?>
                <tr class="border-b border-surface-200">
                    <td class="py-2 pr-4 text-white"><?= e($instrument['symbol']) ?></td>
                    <td class="py-2 pr-4"><?= e($instrument['class_label']) ?></td>
                    <td class="py-2 pr-4"><?= e($instrument['current_price']) ?></td>
                    <td class="py-2 pr-4">1:<?= e((string) $instrument['leverage_max']) ?></td>
                    <td class="py-2 pr-4"><?= ((int) $instrument['is_active']) === 1 ? 'Yes' : 'No' ?></td>
                    <td class="py-2 pr-4">
                        <form method="POST" action="/admin/forex/instruments/toggle" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e((string) $instrument['id']) ?>">
                            <button type="submit" class="btn-secondary !py-1 !px-2 text-xs">Toggle Active</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->stop() ?>
