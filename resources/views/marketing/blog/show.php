<?php $this->layout('layouts/app', ['title' => $post['title']]) ?>
<?php $this->start('body') ?>

<a href="/blog" class="text-sm text-slate-400 hover:text-white">&larr; Blog</a>

<article class="card mt-4">
    <div class="text-xs text-slate-500 mb-2"><?= e($post['published_at']) ?></div>
    <h1 class="text-3xl font-bold text-white mb-6"><?= e($post['title']) ?></h1>
    <div class="prose prose-invert text-slate-300 space-y-4">
        <?php foreach (explode("\n\n", $post['body']) as $paragraph): ?>
            <p><?= e($paragraph) ?></p>
        <?php endforeach; ?>
    </div>
</article>

<?php $this->stop() ?>
