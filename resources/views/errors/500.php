<?php $this->layout('layouts/app', ['title' => 'Something Went Wrong']) ?>
<?php $this->start('body') ?>

<section class="text-center py-20">
    <div class="text-5xl font-bold text-white mb-4">500</div>
    <p class="text-slate-400 mb-8">Something went wrong on our end. The issue has been logged.</p>
    <a href="/" class="btn-primary">Back to Home</a>
</section>

<?php $this->stop() ?>
