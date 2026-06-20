<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="card !p-4">
        <div class="text-xs text-slate-400 uppercase tracking-wide">Main</div>
        <div class="text-xl font-semibold text-white mt-1"><?= e(money($balances['main'])) ?></div>
    </div>
    <div class="card !p-4">
        <div class="text-xs text-slate-400 uppercase tracking-wide">Investment</div>
        <div class="text-xl font-semibold text-white mt-1"><?= e(money($balances['investment'])) ?></div>
    </div>
    <div class="card !p-4">
        <div class="text-xs text-slate-400 uppercase tracking-wide">Forex</div>
        <div class="text-xl font-semibold text-white mt-1"><?= e(money($balances['forex'])) ?></div>
    </div>
    <div class="card !p-4">
        <div class="text-xs text-slate-400 uppercase tracking-wide">Real Estate</div>
        <div class="text-xl font-semibold text-white mt-1"><?= e(money($balances['realestate'])) ?></div>
    </div>
</div>
