<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> · <?= e($appName) ?> Admin</title>
    <link rel="stylesheet" href="<?= e(vite_asset('resources/css/app.css')) ?>">
</head>
<body class="min-h-screen bg-surface-0 text-slate-100 font-sans" x-data="{ drawer: false }">
    <div class="sim-banner"><?= e($simulatedBanner) ?></div>
    <header class="sticky top-0 z-50 border-b border-surface-200 bg-surface-0/95 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 h-14 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="md:hidden inline-flex items-center justify-center w-10 h-10 -ml-2 rounded-lg text-slate-200 hover:bg-surface-200"
                    @click="drawer = !drawer"
                    :aria-expanded="drawer"
                    aria-label="Toggle admin menu"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <a href="/admin" class="font-bold text-lg text-white tracking-tight">Meridian<span class="text-brand-500">Capital</span> <span class="text-slate-500 text-sm font-normal">Admin</span></a>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <a href="/dashboard/investment" class="topnav-link hidden sm:inline">Back to app</a>
                <form method="POST" action="/logout" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-secondary !py-1 !px-3">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div
        x-show="drawer"
        x-cloak
        class="md:hidden fixed inset-0 z-40 bg-black/60"
        @click="drawer = false"
    ></div>
    <aside
        x-show="drawer"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        class="md:hidden fixed inset-y-0 left-0 z-50 w-64 bg-surface-50 border-r border-surface-200 px-3 py-4 overflow-y-auto"
    >
        <div class="text-xs uppercase text-brand-400 font-semibold mb-2 px-3">Admin</div>
        <nav class="flex flex-col gap-1 text-sm" @click="drawer = false">
            <?= $this->insert('partials/admin-nav-links') ?>
        </nav>
    </aside>

    <div class="mx-auto max-w-7xl flex gap-6 px-4 py-6">
        <aside class="hidden md:block w-52 shrink-0">
            <nav class="flex flex-col gap-1 text-sm">
                <div class="text-xs uppercase text-brand-400 font-semibold mb-2 px-3">Admin</div>
                <?= $this->insert('partials/admin-nav-links') ?>
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
