<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Investment Dashboard') ?> · <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= e(vite_asset('resources/css/app.css')) ?>">
</head>
<body class="min-h-screen bg-surface-0 text-slate-100 font-sans">
    <div class="sim-banner"><?= e($simulatedBanner) ?></div>
    <?= $this->insert('partials/topbar') ?>

    <div class="mx-auto max-w-6xl flex gap-6 px-4 py-6 pb-24 md:pb-6">
        <aside class="hidden md:block w-48 shrink-0">
            <nav class="flex flex-col gap-1 text-sm">
                <div class="text-xs uppercase text-brand-400 font-semibold mb-2 px-3">Investment</div>
                <a href="/dashboard/investment" class="sidebar-link <?= is_active_path('/dashboard/investment', true) ? 'is-active' : '' ?>">Overview</a>
                <a href="/dashboard/investment/plans" class="sidebar-link <?= is_active_path('/dashboard/investment/plans') ? 'is-active' : '' ?>">Plans</a>
                <a href="/dashboard/investment/subscriptions" class="sidebar-link <?= is_active_path('/dashboard/investment/subscriptions') ? 'is-active' : '' ?>">My Subscriptions</a>
                <a href="/dashboard/investment/referrals" class="sidebar-link <?= is_active_path('/dashboard/investment/referrals') ? 'is-active' : '' ?>">Referrals</a>
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

    <nav class="bottom-nav grid-cols-4">
        <a href="/dashboard/investment" class="bottom-nav-item <?= is_active_path('/dashboard/investment', true) ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            Overview
        </a>
        <a href="/dashboard/investment/plans" class="bottom-nav-item <?= is_active_path('/dashboard/investment/plans') ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m6 10V11M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            Plans
        </a>
        <a href="/dashboard/investment/subscriptions" class="bottom-nav-item <?= is_active_path('/dashboard/investment/subscriptions') ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            Subscriptions
        </a>
        <a href="/dashboard/investment/referrals" class="bottom-nav-item <?= is_active_path('/dashboard/investment/referrals') ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-3a4 4 0 100-8 4 4 0 000 8zm6 3a4 4 0 10-8 0" /></svg>
            Referrals
        </a>
    </nav>

    <script type="module" src="<?= e(vite_asset('resources/js/app.js')) ?>"></script>
</body>
</html>
