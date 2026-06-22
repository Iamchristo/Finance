<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Real Estate Dashboard') ?> · <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= e(vite_asset('resources/css/app.css')) ?>">
</head>
<body class="min-h-screen bg-surface-0 text-slate-100 font-sans">
    <div class="sim-banner"><?= e($simulatedBanner) ?></div>
    <?= $this->insert('partials/topbar') ?>

    <div class="mx-auto max-w-6xl flex gap-6 px-4 py-6 pb-24 md:pb-6">
        <aside class="hidden md:block w-48 shrink-0">
            <nav class="flex flex-col gap-1 text-sm">
                <div class="text-xs uppercase text-brand-400 font-semibold mb-2 px-3">Real Estate</div>
                <a href="/dashboard/realestate" class="sidebar-link <?= is_active_path('/dashboard/realestate', true) ? 'is-active' : '' ?>">Overview</a>
                <a href="/dashboard/realestate/properties" class="sidebar-link <?= is_active_path('/dashboard/realestate/properties') ? 'is-active' : '' ?>">Properties</a>
                <a href="/dashboard/realestate/portfolio" class="sidebar-link <?= is_active_path('/dashboard/realestate/portfolio') ? 'is-active' : '' ?>">My Portfolio</a>
                <a href="/dashboard/realestate/marketplace" class="sidebar-link <?= is_active_path('/dashboard/realestate/marketplace') ? 'is-active' : '' ?>">Marketplace</a>
                <a href="/dashboard/realestate/listings" class="sidebar-link <?= is_active_path('/dashboard/realestate/listings') ? 'is-active' : '' ?>">My Listings</a>
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

    <nav class="bottom-nav grid-cols-5">
        <a href="/dashboard/realestate" class="bottom-nav-item <?= is_active_path('/dashboard/realestate', true) ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            Overview
        </a>
        <a href="/dashboard/realestate/properties" class="bottom-nav-item <?= is_active_path('/dashboard/realestate/properties') ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V8l-7-5-7 5v13M9 21v-6h6v6" /></svg>
            Properties
        </a>
        <a href="/dashboard/realestate/portfolio" class="bottom-nav-item <?= is_active_path('/dashboard/realestate/portfolio') ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            Portfolio
        </a>
        <a href="/dashboard/realestate/marketplace" class="bottom-nav-item <?= is_active_path('/dashboard/realestate/marketplace') ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l4-4 4 2 6-6m0 0h-4m4 0v4" /></svg>
            Marketplace
        </a>
        <a href="/dashboard/realestate/listings" class="bottom-nav-item <?= is_active_path('/dashboard/realestate/listings') ? 'is-active' : '' ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
            Listings
        </a>
    </nav>

    <script type="module" src="<?= e(vite_asset('resources/js/app.js')) ?>"></script>
</body>
</html>
