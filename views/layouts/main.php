<?php
/**
 * ============================================================================
 * views/layouts/main.php — Layout HTML principal
 * ============================================================================
 * Variables del controlador:
 *   - $hasPhotoHero  bool   → header transparente sobre hero full-bleed
 *   - $pageStyles    array  → hojas extra (Home/auth sobrescriben el default)
 *   - $bodyClass     string → clase extra en <body>
 * ============================================================================
 */
$pageTitle = $pageTitle ?? APP_NAME;
$flash = get_flash();
$hasPhotoHero = !empty($hasPhotoHero);

/* Default: shell de contenido público (Fase 3). Home/auth pasan su propio set. */
$pageStyles = $pageStyles ?? [
    'css/components/sections.css',
    'css/components/cards.css',
    'css/pages/content.css',
];

$bodyClasses = ['has-fixed-header', 'page-modular'];
if (!empty($bodyClass)) {
    $bodyClasses[] = (string) $bodyClass;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Misterios Del Mar — alfabetización oceánica, biodiversidad y acción ambiental.">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/header.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/footer.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/buttons.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/forms.css') ?>">
    <?php foreach ($pageStyles as $sheet): ?>
        <link rel="stylesheet" href="<?= asset($sheet) ?>">
    <?php endforeach; ?>

    <style>
        :root {
            --img-placeholder: url('<?= e(asset('img/placeholder-marine.jpg')) ?>');
        }
    </style>
</head>
<body class="<?= e(implode(' ', $bodyClasses)) ?>">
    <?php require VIEWS_PATH . '/partials/header.php'; ?>

    <?php if ($flash): ?>
        <div class="flash flash--<?= e($flash['type']) ?>" role="status">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <main id="contenido-principal" class="main">
        <?= $content ?>
    </main>

    <?php require VIEWS_PATH . '/partials/footer.php'; ?>

    <script src="<?= asset('js/header-scroll.js') ?>" defer></script>
    <script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
