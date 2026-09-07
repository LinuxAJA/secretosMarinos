<?php
/**
 * ============================================================================
 * views/partials/header.php — Barra de navegación fija
 * ============================================================================
 * Estructura anti-sobrepoblación (Paso 8):
 *   marca | nav primaria + «Más» | acciones de cuenta (siempre visibles en desktop)
 *
 * - data-site-header → header-scroll.js
 * - data-force-scrolled="1" si no hay hero fotográfico
 * - data-nav-toggle / data-nav → assets/js/main.js
 * ============================================================================
 */
$usuario = current_user();
$hasPhotoHero = !empty($hasPhotoHero);
$headerClasses = 'site-header';
if (!$hasPhotoHero) {
    $headerClasses .= ' scrolled';
}
?>
<header
    class="<?= e($headerClasses) ?>"
    data-site-header
    <?php if (!$hasPhotoHero): ?>data-force-scrolled="1"<?php endif; ?>
>
    <div class="container site-header__inner">
        <a class="brand" href="<?= url('/') ?>" aria-label="<?= e(APP_NAME) ?> — inicio">
            <span class="brand__mark" aria-hidden="true"></span>
            <span class="brand__text"><?= e(APP_NAME) ?></span>
        </a>

        <nav id="nav-principal" class="nav" data-nav aria-label="Navegación principal">
            <a class="nav__link" href="<?= url('/') ?>">Inicio</a>
            <a class="nav__link" href="<?= url('/educacion') ?>">Educación</a>
            <a class="nav__link" href="<?= url('/noticias') ?>">Noticias</a>
            <a class="nav__link" href="<?= url('/especies') ?>">Especies</a>
            <a class="nav__link" href="<?= url('/ecosistemas') ?>">Ecosistemas</a>
            <a class="nav__link" href="<?= url('/campanias') ?>">Campañas</a>

            <!-- Secundarios: alineado al pilar «Participar» del footer -->
            <details class="nav-more">
                <summary class="nav-more__summary">Más</summary>
                <div class="nav-more__panel" role="group" aria-label="Más secciones">
                    <a class="nav__link" href="<?= url('/reportes') ?>">Reportes</a>
                    <a class="nav__link" href="<?= url('/insignias') ?>">Insignias</a>
                    <a class="nav__link" href="<?= url('/ranking') ?>">Ranking</a>
                </div>
            </details>

            <!-- Auth también dentro del panel móvil (oculto en desktop vía CSS) -->
            <div class="nav__auth-mobile">
                <?php if ($usuario): ?>
                    <?php if (has_any_role(ROLE_ADMIN, ROLE_DOCENTE)): ?>
                        <a class="nav__link" href="<?= url('/admin') ?>">Admin</a>
                    <?php endif; ?>
                    <a class="nav__link nav__link--accent" href="<?= url('/panel') ?>">
                        <?= e($usuario['nombre'] ?? 'Panel') ?>
                    </a>
                    <form class="nav__logout" method="post" action="<?= url('/logout') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="nav__link nav__link--logout">Salir</button>
                    </form>
                <?php else: ?>
                    <a class="nav__link" href="<?= url('/registro') ?>">Registro</a>
                    <a class="nav__link nav__link--accent" href="<?= url('/login') ?>">Ingresar</a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="header-actions">
            <?php if ($usuario): ?>
                <?php if (has_any_role(ROLE_ADMIN, ROLE_DOCENTE)): ?>
                    <a class="nav__link header-actions__link" href="<?= url('/admin') ?>">Admin</a>
                <?php endif; ?>
                <a class="nav__link nav__link--accent" href="<?= url('/panel') ?>">
                    <?= e($usuario['nombre'] ?? 'Panel') ?>
                </a>
                <form class="nav__logout" method="post" action="<?= url('/logout') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="nav__link nav__link--logout">Salir</button>
                </form>
            <?php else: ?>
                <a class="nav__link header-actions__link" href="<?= url('/registro') ?>">Registro</a>
                <a class="nav__link nav__link--accent" href="<?= url('/login') ?>">Ingresar</a>
            <?php endif; ?>

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
        </div>
    </div>
</header>
