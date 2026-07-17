<?php
/**
 * ============================================================================
 * views/layouts/main.php — Layout HTML principal
 * ============================================================================
 * Envuelve todas las páginas públicas.
 * Las variables disponibles vienen del controlador vía render():
 *   - $pageTitle  → título de la pestaña
 *   - $content    → HTML de la vista específica (home, login, etc.)
 * ============================================================================
 */
$pageTitle = $pageTitle ?? APP_NAME;
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secretos Marinos — alfabetización oceánica, biodiversidad y acción ambiental.">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>

    <!-- Tipografías: Fraunces (títulos expresivos) + Source Sans 3 (cuerpo legible) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>
    <?php require VIEWS_PATH . '/partials/header.php'; ?>

    <?php if ($flash): ?>
        <!-- Mensaje flash de una sola lectura (éxito, error, etc.) -->
        <div class="flash flash--<?= e($flash['type']) ?>" role="status">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <main id="contenido-principal" class="main">
        <!-- Aquí se inyecta la vista concreta (pages/home.php, etc.) -->
        <?= $content ?>
    </main>

    <?php require VIEWS_PATH . '/partials/footer.php'; ?>

    <script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
