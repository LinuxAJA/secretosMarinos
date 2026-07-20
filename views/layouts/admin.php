<?php
/**
 * ============================================================================
 * views/layouts/admin.php — Layout del panel de administración
 * ============================================================================
 */
$pageTitle = $pageTitle ?? 'Admin';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Admin · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="admin-body">
    <div class="admin-shell">
        <?php require VIEWS_PATH . '/partials/admin-sidebar.php'; ?>

        <div class="admin-main">
            <header class="admin-top">
                <div>
                    <p class="admin-top__brand"><?= e(APP_NAME) ?></p>
                    <h1 class="admin-top__title"><?= e($pageTitle) ?></h1>
                </div>
                <a class="btn btn--secondary" href="<?= url('/') ?>">Ver sitio</a>
            </header>

            <?php if ($flash): ?>
                <div class="flash flash--<?= e($flash['type']) ?>" role="status">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-content">
                <?= $content ?>
            </div>
        </div>
    </div>
    <script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
