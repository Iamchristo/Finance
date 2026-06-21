<?php $this->layout('layouts/app', ['title' => 'Page Not Found']) ?>
<?php $this->start('body') ?>

<section class="text-center py-20">
    <div class="text-5xl font-bold text-white mb-4">404</div>
    <p class="text-slate-400 mb-8">The page you're looking for doesn't exist.</p>
    <a href="/" class="btn-primary">Back to Home</a>
</section>

<?php $this->stop() ?>
