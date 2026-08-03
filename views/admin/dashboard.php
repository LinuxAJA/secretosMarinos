<?php
/** Dashboard admin — Pasos 3 a 7 */
$part = $kpis['participacion'] ?? [];
$rep = $part['reportes'] ?? [];
?>
<section class="admin-dashboard">
    <p class="section__lead">
        Gestiona contenidos, participación, gamificación y (si eres admin) la comunidad de usuarios.
    </p>

    <div class="panel-grid">
        <article class="panel-stat">
            <h2 class="panel-stat__label">Contenidos</h2>
            <p class="panel-stat__value"><?= (int) $stats['contenidos'] ?></p>
            <a href="<?= url('/admin/contenidos') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Noticias</h2>
            <p class="panel-stat__value"><?= (int) $stats['noticias'] ?></p>
            <a href="<?= url('/admin/noticias') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Ecosistemas</h2>
            <p class="panel-stat__value"><?= (int) $stats['ecosistemas'] ?></p>
            <a href="<?= url('/admin/ecosistemas') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Especies</h2>
            <p class="panel-stat__value"><?= (int) $stats['especies'] ?></p>
            <a href="<?= url('/admin/especies') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Campañas</h2>
            <p class="panel-stat__value"><?= (int) $stats['campanias'] ?></p>
            <p class="muted">
                <?= (int) ($part['campanias']['activa'] ?? 0) ?> activas ·
                <?= (int) ($part['campanias']['cancelada'] ?? 0) ?> canceladas
            </p>
            <a href="<?= url('/admin/campanias') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Reportes</h2>
            <p class="panel-stat__value"><?= (int) $stats['reportes'] ?></p>
            <p class="muted">
                <?= (int) ($rep['pendiente'] ?? 0) ?> pend. ·
                <?= (int) ($rep['en_revision'] ?? 0) ?> revisión ·
                <?= (int) ($rep['resuelto'] ?? 0) ?> resueltos
            </p>
            <a href="<?= url('/admin/reportes') ?>">Revisar cola</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Insignias</h2>
            <p class="panel-stat__value"><?= (int) $stats['insignias'] ?></p>
            <a href="<?= url('/admin/insignias') ?>">Administrar</a>
        </article>

        <?php if (can_manage_users() && isset($kpis['comunidad'])): ?>
            <article class="panel-stat">
                <h2 class="panel-stat__label">Usuarios</h2>
                <p class="panel-stat__value"><?= (int) $stats['usuarios'] ?></p>
                <p class="muted">
                    <?= (int) ($kpis['comunidad']['usuarios_activos'] ?? 0) ?> activos ·
                    <?= (int) ($kpis['comunidad']['usuarios_inactivos'] ?? 0) ?> inactivos
                </p>
                <a href="<?= url('/admin/usuarios') ?>">Gestionar</a>
            </article>
        <?php endif; ?>

        <article class="panel-stat">
            <h2 class="panel-stat__label">Accesos rápidos</h2>
            <p class="panel-actions" style="margin-top:0.75rem">
                <a class="btn btn--primary" href="<?= url('/admin/estadisticas') ?>">Ver estadísticas</a>
                <a class="btn btn--secondary" href="<?= url('/admin/reportes') ?>">Reportes</a>
                <?php if (can_manage_users()): ?>
                    <a class="btn btn--secondary" href="<?= url('/admin/usuarios') ?>">Usuarios</a>
                <?php endif; ?>
                <?php if (can_adjust_points()): ?>
                    <a class="btn btn--secondary" href="<?= url('/admin/puntos') ?>">Ajustar puntos</a>
                <?php endif; ?>
                <?php if (can_manage_categories()): ?>
                    <a class="btn btn--secondary" href="<?= url('/admin/categorias/crear') ?>">Nueva categoría</a>
                <?php endif; ?>
            </p>
            <?php if (is_docente() && !is_admin()): ?>
                <p class="form-hint">
                    Como docente gestionas tus contenidos, noticias, especies y campañas.
                    Revisas reportes y consultas estadísticas (sin métricas de cuentas).
                    Categorías, ecosistemas e insignias son de solo lectura.
                </p>
            <?php endif; ?>
        </article>
    </div>
</section>
