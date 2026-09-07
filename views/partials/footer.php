<?php
/**
 * ============================================================================
 * views/partials/footer.php — Pie institucional con navegación útil
 * ============================================================================
 * Paso 8 v2: mapa de salida (Explorar / Participar / Cuenta) + identidad.
 * Solo enlaces a rutas existentes; sin redes inventadas.
 * ============================================================================
 */
$usuario = current_user();
?>
<footer class="site-footer">
    <div class="container site-footer__grid">
        <div class="site-footer__col site-footer__col--brand">
            <p class="site-footer__brand"><?= e(APP_NAME) ?></p>
            <p class="site-footer__mission">
                Alfabetización oceánica y acción ambiental en un entorno formativo SENA:
                aprender, observar y participar por el mar.
            </p>
        </div>

        <div class="site-footer__col">
            <p class="site-footer__heading">Explorar</p>
            <nav class="site-footer__nav" aria-label="Explorar contenidos">
                <a href="<?= url('/educacion') ?>">Educación</a>
                <a href="<?= url('/noticias') ?>">Noticias</a>
                <a href="<?= url('/especies') ?>">Especies</a>
                <a href="<?= url('/ecosistemas') ?>">Ecosistemas</a>
            </nav>
        </div>

        <div class="site-footer__col">
            <p class="site-footer__heading">Participar</p>
            <nav class="site-footer__nav" aria-label="Participar">
                <a href="<?= url('/campanias') ?>">Campañas</a>
                <a href="<?= url('/reportes') ?>">Reportes</a>
                <a href="<?= url('/insignias') ?>">Insignias</a>
                <a href="<?= url('/ranking') ?>">Ranking</a>
            </nav>
        </div>

        <div class="site-footer__col">
            <p class="site-footer__heading">Cuenta</p>
            <nav class="site-footer__nav" aria-label="Cuenta">
                <?php if ($usuario): ?>
                    <a href="<?= url('/panel') ?>">Mi panel</a>
                    <?php if (has_any_role(ROLE_ADMIN, ROLE_DOCENTE)): ?>
                        <a href="<?= url('/admin') ?>">Administración</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= url('/login') ?>">Ingresar</a>
                    <a href="<?= url('/registro') ?>">Registro</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <div class="container site-footer__bottom">
        <p class="site-footer__copy">
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> · v<?= e(APP_VERSION) ?>
        </p>
        <p class="site-footer__note">Entorno local de formación · XAMPP</p>
    </div>
</footer>
