<?php
/**
 * ============================================================================
 * views/partials/header.php — Barra de navegación superior
 * ============================================================================
 * Partial = fragmento reutilizable incluido desde el layout.
 * Si hay sesión: muestra panel + logout (POST + CSRF).
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
            <a class="nav__link" href="<?= url('/noticias') ?>">Noticias</a>
            <a class="nav__link" href="<?= url('/especies') ?>">Especies</a>
            <a class="nav__link" href="<?= url('/ecosistemas') ?>">Ecosistemas</a>
            <a class="nav__link" href="<?= url('/campanias') ?>">Campañas</a>
            <a class="nav__link" href="<?= url('/reportes') ?>">Reportes</a>

            <?php if ($usuario): ?>
                <?php if (has_any_role(ROLE_ADMIN, ROLE_DOCENTE)): ?>
                    <a class="nav__link" href="<?= url('/admin') ?>">Admin</a>
                <?php endif; ?>
                <a class="nav__link nav__link--accent" href="<?= url('/panel') ?>">
                    <?= e($usuario['nombre'] ?? 'Panel') ?>
                </a>
                <!-- Logout por POST: evita que un enlace malicioso cierre la sesión -->
                <form class="nav__logout" method="post" action="<?= url('/logout') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="nav__link nav__link--logout">Salir</button>
                </form>
            <?php else: ?>
                <a class="nav__link" href="<?= url('/registro') ?>">Registro</a>
                <a class="nav__link nav__link--accent" href="<?= url('/login') ?>">Ingresar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
