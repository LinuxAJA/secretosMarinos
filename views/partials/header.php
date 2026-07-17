<?php
/**
 * ============================================================================
 * views/partials/header.php — Barra de navegación superior
 * ============================================================================
 * Partial = fragmento reutilizable incluido desde el layout.
 * Más adelante marcaremos el enlace activo según la URL.
 * ============================================================================
 */
$usuario = current_user();
?>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="<?= url('/') ?>" aria-label="<?= e(APP_NAME) ?> — inicio">
            <span class="brand__mark" aria-hidden="true"></span>
            <span class="brand__text"><?= e(APP_NAME) ?></span>
        </a>

        <!-- Botón hamburguesa (visible en móvil) -->
        <button
            class="nav-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="nav-principal"
            data-nav-toggle
        >
            <span class="visually-hidden">Abrir menú</span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>

        <nav id="nav-principal" class="nav" data-nav aria-label="Navegación principal">
            <a class="nav__link" href="<?= url('/') ?>">Inicio</a>
            <a class="nav__link" href="<?= url('/educacion') ?>">Educación</a>
            <a class="nav__link" href="<?= url('/especies') ?>">Especies</a>
            <a class="nav__link" href="<?= url('/ecosistemas') ?>">Ecosistemas</a>
            <a class="nav__link" href="<?= url('/campanias') ?>">Campañas</a>
            <a class="nav__link" href="<?= url('/reportes') ?>">Reportes</a>

            <?php if ($usuario): ?>
                <a class="nav__link nav__link--accent" href="<?= url('/panel') ?>">
                    Hola, <?= e($usuario['nombre'] ?? 'Usuario') ?>
                </a>
            <?php else: ?>
                <a class="nav__link nav__link--accent" href="<?= url('/login') ?>">Ingresar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
