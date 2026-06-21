<?php $this->layout('layouts/app', ['title' => 'Privacy Policy']) ?>
<?php $this->start('body') ?>

<article class="card space-y-4 text-slate-300">
    <h1 class="text-3xl font-bold text-white mb-2">Privacy Policy</h1>
    <p class="text-amber-300 text-sm font-medium"><?= e($simulatedBanner) ?></p>

    <h2 class="text-xl font-bold text-white pt-4">Information we collect</h2>
    <p>We collect the information you provide when registering (name, email) and the activity you generate while using the simulated dashboards (subscriptions, orders, listings, ledger entries).</p>

    <h2 class="text-xl font-bold text-white pt-4">How we use it</h2>
    <p>Account information is used solely to operate your account on this demonstration platform — authenticating you, rendering your dashboards, and recording your simulated activity in the audit log.</p>

    <h2 class="text-xl font-bold text-white pt-4">What we don't do</h2>
    <p>We do not sell your information, and because no real money moves through this platform, we never collect real payment card or bank account details.</p>

    <h2 class="text-xl font-bold text-white pt-4">Security</h2>
    <p>Passwords are hashed, sessions are regenerated on privilege changes, and state-changing requests require a CSRF token, consistent with standard web application security practice.</p>

    <h2 class="text-xl font-bold text-white pt-4">Contact</h2>
    <p>This is a demonstration platform; for any questions about this policy, refer to the project documentation.</p>
</article>

<?php $this->stop() ?>
