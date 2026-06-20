<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Trading Dashboard') ?> · <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= e(vite_asset('resources/css/app.css')) ?>">
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans">
    <?= $this->insert('partials/topbar') ?>
    <div class="sim-banner"><?= e($simulatedBanner) ?> Simulated prices — not live market data.</div>

    <div class="mx-auto max-w-6xl flex gap-6 px-4 py-6">
        <aside class="w-48 shrink-0">
            <nav class="flex flex-col gap-1 text-sm">
                <div class="text-xs uppercase text-sky-400 font-semibold mb-2 px-3">Trading</div>
                <a href="/dashboard/forex" class="px-3 py-2 rounded-lg hover:bg-slate-800">Overview</a>
                <a href="/dashboard/forex/markets" class="px-3 py-2 rounded-lg hover:bg-slate-800">Markets</a>
                <a href="/dashboard/forex/positions" class="px-3 py-2 rounded-lg hover:bg-slate-800">Positions</a>
                <a href="/dashboard/forex/orders" class="px-3 py-2 rounded-lg hover:bg-slate-800">Order History</a>
                <a href="/dashboard/forex/watchlist" class="px-3 py-2 rounded-lg hover:bg-slate-800">Watchlist</a>
                <a href="/dashboard/forex/strategies" class="px-3 py-2 rounded-lg hover:bg-slate-800">Strategies</a>
            </nav>
        </aside>

        <main class="flex-1 min-w-0">
            <?php if ($flashError = $_SESSION['_flash']['error'] ?? null): unset($_SESSION['_flash']['error']); ?>
                <div class="rounded-lg bg-rose-950/60 border border-rose-700 text-rose-200 px-4 py-3 mb-4"><?= e($flashError) ?></div>
            <?php endif; ?>
            <?php if ($flashSuccess = $_SESSION['_flash']['success'] ?? null): unset($_SESSION['_flash']['success']); ?>
                <div class="rounded-lg bg-emerald-950/60 border border-emerald-700 text-emerald-200 px-4 py-3 mb-4"><?= e($flashSuccess) ?></div>
            <?php endif; ?>
            <?= $this->section('body') ?>
        </main>
    </div>

    <script type="module" src="<?= e(vite_asset('resources/js/app.js')) ?>"></script>
</body>
</html>
