<header class="sticky top-0 z-50 border-b border-surface-200 bg-surface-0/95 backdrop-blur" x-data="{ drawer: false }">
    <div class="mx-auto max-w-6xl px-4 h-14 flex items-center justify-between gap-4">
        <a href="/" class="font-bold text-lg text-white tracking-tight shrink-0">Meridian<span class="text-brand-500">Capital</span></a>

        <nav class="hidden md:flex items-center gap-5">
            <a href="/invest" class="topnav-link <?= is_active_path('/invest') ? 'is-active' : '' ?>">Investment</a>
            <a href="/trading" class="topnav-link <?= is_active_path('/trading') ? 'is-active' : '' ?>">Trading</a>
            <a href="/real-estate" class="topnav-link <?= is_active_path('/real-estate') ? 'is-active' : '' ?>">Real Estate</a>
            <?php if (($_SESSION['user_id'] ?? null) !== null): ?>
                <a href="/dashboard/investment" class="topnav-link <?= is_active_path('/dashboard') ? 'is-active' : '' ?>">Dashboard</a>
                <a href="/wallet" class="topnav-link <?= is_active_path('/wallet') ? 'is-active' : '' ?>">Wallet</a>
            <?php endif; ?>
        </nav>

        <div class="hidden md:flex items-center gap-3 text-sm shrink-0">
            <?php if (($_SESSION['user_id'] ?? null) !== null): ?>
                <form method="POST" action="/logout" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-secondary !py-1.5 !px-4">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login" class="topnav-link">Login</a>
                <a href="/register" class="btn-primary !py-1.5 !px-4">Get Started</a>
            <?php endif; ?>
        </div>

        <button
            type="button"
            class="md:hidden inline-flex items-center justify-center w-10 h-10 -mr-2 rounded-lg text-slate-200 hover:bg-surface-200"
            @click="drawer = !drawer"
            :aria-expanded="drawer"
            aria-label="Toggle menu"
        >
            <svg x-show="!drawer" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="drawer" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div
        x-show="drawer"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="md:hidden border-t border-surface-200 bg-surface-0 px-4 py-3"
        @click.outside="drawer = false"
    >
        <nav class="flex flex-col gap-1">
            <a href="/invest" class="drawer-link <?= is_active_path('/invest') ? 'is-active' : '' ?>">Investment</a>
            <a href="/trading" class="drawer-link <?= is_active_path('/trading') ? 'is-active' : '' ?>">Trading</a>
            <a href="/real-estate" class="drawer-link <?= is_active_path('/real-estate') ? 'is-active' : '' ?>">Real Estate</a>
            <div class="border-t border-surface-200 my-2"></div>
            <?php if (($_SESSION['user_id'] ?? null) !== null): ?>
                <a href="/dashboard/investment" class="drawer-link">Investment Dashboard</a>
                <a href="/dashboard/forex" class="drawer-link">Trading Dashboard</a>
                <a href="/dashboard/realestate" class="drawer-link">Real Estate Dashboard</a>
                <a href="/wallet" class="drawer-link">Wallet</a>
                <form method="POST" action="/logout" class="mt-2">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-secondary w-full">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login" class="drawer-link">Login</a>
                <a href="/register" class="btn-primary w-full mt-2">Get Started</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
