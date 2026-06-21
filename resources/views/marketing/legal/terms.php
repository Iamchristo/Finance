<?php $this->layout('layouts/app', ['title' => 'Terms of Service']) ?>
<?php $this->start('body') ?>

<article class="card space-y-4 text-slate-300">
    <h1 class="text-3xl font-bold text-white mb-2">Terms of Service</h1>
    <p class="text-amber-300 text-sm font-medium"><?= e($simulatedBanner) ?></p>

    <h2 class="text-xl font-bold text-white pt-4">1. Acceptance</h2>
    <p>By creating an account or otherwise using this platform, you acknowledge that it is a simulated demonstration product and agree to these terms.</p>

    <h2 class="text-xl font-bold text-white pt-4">2. Nature of the platform</h2>
    <p>All wallet balances, investment plans, trading instruments, positions, properties, and marketplace listings are simulated. No real currency, security, or property changes hands at any point.</p>

    <h2 class="text-xl font-bold text-white pt-4">3. Accounts</h2>
    <p>You are responsible for maintaining the confidentiality of your account credentials and for all activity that occurs under your account.</p>

    <h2 class="text-xl font-bold text-white pt-4">4. Acceptable use</h2>
    <p>You agree not to attempt to disrupt, reverse engineer for malicious purposes, or use the platform to misrepresent it as a real financial service to third parties.</p>

    <h2 class="text-xl font-bold text-white pt-4">5. No warranty</h2>
    <p>The platform is provided "as is" for demonstration purposes, without warranty of any kind, express or implied.</p>

    <h2 class="text-xl font-bold text-white pt-4">6. Changes</h2>
    <p>These terms may be updated at any time as the platform evolves; continued use constitutes acceptance of the revised terms.</p>
</article>

<?php $this->stop() ?>
