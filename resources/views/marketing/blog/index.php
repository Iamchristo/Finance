<?php $this->layout('layouts/app', ['title' => 'Blog']) ?>
<?php $this->start('body') ?>

<section class="text-center py-10">
    <h1 class="text-3xl font-bold text-white tracking-tight mb-4">Blog</h1>
    <p class="text-slate-400 max-w-2xl mx-auto">Notes on investing, trading, and real estate mechanics — explained through the lens of this simulated platform.</p>
</section>

<section class="grid sm:grid-cols-2 gap-6">
    <?php foreach ($posts as $post): ?>
        <a href="/blog/<?= e($post['slug']) ?>" class="card hover:shadow-elevated transition block">
            <div class="text-xs text-slate-500 mb-2"><?= e($post['published_at']) ?></div>
            <h2 class="text-lg font-bold text-white mb-2"><?= e($post['title']) ?></h2>
            <p class="text-sm text-slate-400"><?= e($post['excerpt']) ?></p>
        </a>
    <?php endforeach; ?>
</section>

<?php $this->stop() ?>
