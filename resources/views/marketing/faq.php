<?php $this->layout('layouts/app', ['title' => 'FAQ']) ?>
<?php $this->start('body') ?>

<section class="text-center py-10">
    <h1 class="text-3xl font-bold text-white tracking-tight mb-4">Frequently Asked Questions</h1>
    <p class="text-slate-400 max-w-2xl mx-auto"><?= e($simulatedBanner) ?></p>
</section>

<?php foreach ($groups as $groupName => $items): ?>
    <section class="mb-8">
        <h2 class="text-lg font-bold text-white mb-4"><?= e($groupName) ?></h2>
        <div class="space-y-3">
            <?php foreach ($items as $item): ?>
                <details class="card">
                    <summary class="cursor-pointer text-white font-medium"><?= e($item['question']) ?></summary>
                    <p class="text-sm text-slate-400 mt-3"><?= e($item['answer']) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<?php $this->stop() ?>
