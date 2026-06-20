<header class="border-b border-slate-800 bg-slate-900/80">
    <div class="mx-auto max-w-6xl px-4 py-3 flex items-center justify-between gap-4">
        <a href="/" class="font-bold text-lg text-white tracking-tight">Meridian<span class="text-emerald-400">Capital</span></a>

        <nav class="hidden md:flex items-center gap-5 text-sm text-slate-300">
            <a href="/invest" class="hover:text-white">Investment</a>
            <a href="/trading" class="hover:text-white">Trading</a>
            <a href="/real-estate" class="hover:text-white">Real Estate</a>
        </nav>

        <div class="flex items-center gap-3 text-sm">
            <?php if (($_SESSION['user_id'] ?? null) !== null): ?>
                <a href="/dashboard/investment" class="hidden sm:inline text-slate-300 hover:text-white">Investment</a>
                <a href="/dashboard/forex" class="hidden sm:inline text-slate-300 hover:text-white">Trading</a>
                <a href="/dashboard/realestate" class="hidden sm:inline text-slate-300 hover:text-white">Real Estate</a>
                <a href="/wallet" class="text-slate-300 hover:text-white">Wallet</a>
                <form method="POST" action="/logout" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-secondary !py-1 !px-3">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login" class="text-slate-300 hover:text-white">Login</a>
                <a href="/register" class="btn-primary !py-1.5 !px-4">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
</header>
