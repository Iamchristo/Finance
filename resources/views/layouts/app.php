<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? $appName) ?> · <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= e(vite_asset('resources/css/app.css')) ?>">
</head>
<body class="min-h-screen bg-surface-0 text-slate-100 font-sans">
    <div class="sim-banner"><?= e($simulatedBanner) ?></div>
    <?= $this->insert('partials/topbar') ?>

    <?php if ($flashError = $_SESSION['_flash']['error'] ?? null): unset($_SESSION['_flash']['error']); ?>
        <div class="mx-auto max-w-6xl mt-4 px-4">
            <div class="rounded-lg bg-rose-950/60 border border-rose-700 text-rose-200 px-4 py-3"><?= e($flashError) ?></div>
        </div>
    <?php endif; ?>
    <?php if ($flashSuccess = $_SESSION['_flash']['success'] ?? null): unset($_SESSION['_flash']['success']); ?>
        <div class="mx-auto max-w-6xl mt-4 px-4">
            <div class="rounded-lg bg-emerald-950/60 border border-emerald-700 text-emerald-200 px-4 py-3"><?= e($flashSuccess) ?></div>
        </div>
    <?php endif; ?>

    <main class="mx-auto max-w-6xl px-4 py-8">
        <?= $this->section('body') ?>
    </main>

    <?= $this->insert('partials/footer') ?>

    <script type="module" src="<?= e(vite_asset('resources/js/app.js')) ?>"></script>
</body>
</html>
