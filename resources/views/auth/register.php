<?php $this->layout('layouts/app', ['title' => 'Create your account']) ?>
<?php $this->start('body') ?>

<div class="max-w-md mx-auto card">
    <h1 class="text-2xl font-bold text-white mb-1">Create your account</h1>
    <p class="text-sm text-slate-400 mb-6">One account, one wallet — investment, trading, and real estate.</p>

    <form method="POST" action="/register" class="space-y-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm text-slate-300 mb-1">First name</label>
                <input type="text" name="first_name" required class="input">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Last name</label>
                <input type="text" name="last_name" required class="input">
            </div>
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Email</label>
            <input type="email" name="email" required class="input">
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Password</label>
            <input type="password" name="password" minlength="10" required class="input">
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Referral code (optional)</label>
            <input type="text" name="referral_code" class="input">
        </div>
        <button type="submit" class="btn-primary w-full">Create account</button>
    </form>

    <p class="text-sm text-slate-400 mt-4">Already have an account? <a href="/login" class="text-brand-400 hover:underline">Sign in</a></p>
</div>

<?php $this->stop() ?>
