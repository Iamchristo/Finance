<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> · <?= e($appName) ?> Admin</title>
    <link rel="stylesheet" href="<?= e(vite_asset('resources/css/app.css')) ?>">
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans">
    <header class="border-b border-slate-800 bg-slate-900/80">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-4">
            <a href="/admin" class="font-bold text-lg text-white tracking-tight">Meridian<span class="text-emerald-400">Capital</span> <span class="text-slate-500 text-sm font-normal">Admin</span></a>
            <div class="flex items-center gap-3 text-sm">
                <a href="/dashboard/investment" class="text-slate-300 hover:text-white">Back to app</a>
                <form method="POST" action="/logout" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-secondary !py-1 !px-3">Logout</button>
                </form>
            </div>
        </div>
    </header>
    <div class="sim-banner"><?= e($simulatedBanner) ?></div>

    <div class="mx-auto max-w-7xl flex gap-6 px-4 py-6">
        <aside class="w-52 shrink-0">
            <nav class="flex flex-col gap-1 text-sm">
                <div class="text-xs uppercase text-amber-400 font-semibold mb-2 px-3">Admin</div>
                <a href="/admin" class="px-3 py-2 rounded-lg hover:bg-slate-800">Dashboard</a>
                <a href="/admin/users" class="px-3 py-2 rounded-lg hover:bg-slate-800">Users</a>
                <a href="/admin/kyc" class="px-3 py-2 rounded-lg hover:bg-slate-800">KYC Review</a>
                <a href="/admin/ledger" class="px-3 py-2 rounded-lg hover:bg-slate-800">Ledger Explorer</a>
                <a href="/admin/investment/plans" class="px-3 py-2 rounded-lg hover:bg-slate-800">Investment Plans</a>
                <a href="/admin/forex/instruments" class="px-3 py-2 rounded-lg hover:bg-slate-800">Forex Instruments</a>
                <a href="/admin/forex/strategies" class="px-3 py-2 rounded-lg hover:bg-slate-800">Forex Strategies</a>
                <a href="/admin/realestate/properties" class="px-3 py-2 rounded-lg hover:bg-slate-800">Properties</a>
                <a href="/admin/realestate/listings" class="px-3 py-2 rounded-lg hover:bg-slate-800">Listing Moderation</a>
                <a href="/admin/audit" class="px-3 py-2 rounded-lg hover:bg-slate-800">Audit Log</a>
                <a href="/admin/settings" class="px-3 py-2 rounded-lg hover:bg-slate-800">Settings</a>
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
