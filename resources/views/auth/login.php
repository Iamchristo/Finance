<?php $this->layout('layouts/app', ['title' => 'Sign in']) ?>
<?php $this->start('body') ?>

<div class="max-w-md mx-auto card">
    <h1 class="text-2xl font-bold text-white mb-1">Sign in</h1>
    <p class="text-sm text-slate-400 mb-6">Access your investment, trading, and real estate dashboards.</p>

    <form method="POST" action="/login" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Email</label>
            <input type="email" name="email" required class="input">
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Password</label>
            <input type="password" name="password" required class="input">
        </div>
        <button type="submit" class="btn-primary w-full">Sign in</button>
    </form>

    <p class="text-sm text-slate-400 mt-4">No account yet? <a href="/register" class="text-emerald-400 hover:underline">Create one</a></p>
</div>

<?php $this->stop() ?>
