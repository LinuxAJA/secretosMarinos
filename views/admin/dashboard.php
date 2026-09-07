<?php
/**
 * Dashboard admin — mosaico denso (estilo tiles Home)
 */
$part = $kpis['participacion'] ?? [];
$rep = $part['reportes'] ?? [];
$camp = $part['campanias'] ?? [];
$pendientes = (int) ($rep['pendiente'] ?? 0);
$enRevision = (int) ($rep['en_revision'] ?? 0);
$cola = $pendientes + $enRevision;
?>
<section class="dash" aria-label="Resumen de administración">
    <header class="dash-intro">
        <div class="dash-intro__copy">
            <p class="dash-intro__kicker">Centro de control</p>
            <h2 class="dash-intro__title">Panorama de la plataforma</h2>
            <p class="dash-intro__lead">
                Contenidos, catálogo, participación y gamificación en un vistazo.
                <?php if (is_docente() && !is_admin()): ?>
                    Como docente gestionas lo propio; categorías, ecosistemas e insignias son de solo lectura.
                <?php endif; ?>
            </p>
        </div>
        <?php if (can_view_stats()): ?>
            <a class="btn btn--sol" href="<?= url('/admin/estadisticas') ?>">Ver estadísticas</a>
        <?php endif; ?>
    </header>

    <?php if ($cola > 0): ?>
        <aside class="dash-alert" role="status">
            <div class="dash-alert__body">
                <p class="dash-alert__label">Atención en reportes</p>
                <p class="dash-alert__text">
                    <strong><?= $cola ?></strong> en cola
                    (<?= $pendientes ?> pendientes · <?= $enRevision ?> en revisión)
                </p>
            </div>
            <a class="dash-alert__cta" href="<?= url('/admin/reportes') ?>">Revisar cola →</a>
        </aside>
    <?php endif; ?>

    <div class="dash-zone">
        <h3 class="dash-zone__title">Indicadores</h3>
        <div class="dash-mosaic">
            <a class="dash-kpi" href="<?= url('/admin/contenidos') ?>">
                <span class="dash-kpi__num" aria-hidden="true">01</span>
                <span class="dash-kpi__label">Contenidos</span>
                <span class="dash-kpi__value"><?= (int) $stats['contenidos'] ?></span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <a class="dash-kpi" href="<?= url('/admin/noticias') ?>">
                <span class="dash-kpi__num" aria-hidden="true">02</span>
                <span class="dash-kpi__label">Noticias</span>
                <span class="dash-kpi__value"><?= (int) $stats['noticias'] ?></span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <a class="dash-kpi" href="<?= url('/admin/ecosistemas') ?>">
                <span class="dash-kpi__num" aria-hidden="true">03</span>
                <span class="dash-kpi__label">Ecosistemas</span>
                <span class="dash-kpi__value"><?= (int) $stats['ecosistemas'] ?></span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <a class="dash-kpi" href="<?= url('/admin/especies') ?>">
                <span class="dash-kpi__num" aria-hidden="true">04</span>
                <span class="dash-kpi__label">Especies</span>
                <span class="dash-kpi__value"><?= (int) $stats['especies'] ?></span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <a class="dash-kpi dash-kpi--sol" href="<?= url('/admin/reportes') ?>">
                <span class="dash-kpi__num" aria-hidden="true">05</span>
                <span class="dash-kpi__label">Reportes</span>
                <span class="dash-kpi__value"><?= (int) $stats['reportes'] ?></span>
                <span class="dash-kpi__meta">
                    <?= $pendientes ?> pend. · <?= $enRevision ?> revisión ·
                    <?= (int) ($rep['resuelto'] ?? 0) ?> resueltos
                </span>
                <span class="dash-kpi__go">Revisar cola →</span>
            </a>
            <a class="dash-kpi" href="<?= url('/admin/campanias') ?>">
                <span class="dash-kpi__num" aria-hidden="true">06</span>
                <span class="dash-kpi__label">Campañas</span>
                <span class="dash-kpi__value"><?= (int) $stats['campanias'] ?></span>
                <span class="dash-kpi__meta">
                    <?= (int) ($camp['activa'] ?? 0) ?> activas ·
                    <?= (int) ($camp['cancelada'] ?? 0) ?> canceladas
                </span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <a class="dash-kpi" href="<?= url('/admin/insignias') ?>">
                <span class="dash-kpi__num" aria-hidden="true">07</span>
                <span class="dash-kpi__label">Insignias</span>
                <span class="dash-kpi__value"><?= (int) $stats['insignias'] ?></span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <?php if (can_manage_users() && isset($kpis['comunidad'])): ?>
                <a class="dash-kpi" href="<?= url('/admin/usuarios') ?>">
                    <span class="dash-kpi__num" aria-hidden="true">08</span>
                    <span class="dash-kpi__label">Usuarios</span>
                    <span class="dash-kpi__value"><?= (int) $stats['usuarios'] ?></span>
                    <span class="dash-kpi__meta">
                        <?= (int) ($kpis['comunidad']['usuarios_activos'] ?? 0) ?> activos ·
                        <?= (int) ($kpis['comunidad']['usuarios_inactivos'] ?? 0) ?> inactivos
                    </span>
                    <span class="dash-kpi__go">Gestionar →</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dash-zone">
        <h3 class="dash-zone__title">Accesos rápidos</h3>
        <div class="dash-mosaic dash-mosaic--actions">
            <a class="dash-action" href="<?= url('/admin/reportes') ?>">
                <span class="dash-action__label">Cola de reportes</span>
                <span class="dash-action__hint">Revisar pendientes</span>
            </a>
            <?php if (can_view_stats()): ?>
                <a class="dash-action" href="<?= url('/admin/estadisticas') ?>">
                    <span class="dash-action__label">Estadísticas</span>
                    <span class="dash-action__hint">Métricas detalladas</span>
                </a>
            <?php endif; ?>
            <?php if (can_manage_users()): ?>
                <a class="dash-action" href="<?= url('/admin/usuarios') ?>">
                    <span class="dash-action__label">Usuarios</span>
                    <span class="dash-action__hint">Roles y estado</span>
                </a>
            <?php endif; ?>
            <?php if (can_adjust_points()): ?>
                <a class="dash-action" href="<?= url('/admin/puntos') ?>">
                    <span class="dash-action__label">Ajustar puntos</span>
                    <span class="dash-action__hint">Corrección manual</span>
                </a>
            <?php endif; ?>
            <?php if (can_manage_categories()): ?>
                <a class="dash-action" href="<?= url('/admin/categorias/crear') ?>">
                    <span class="dash-action__label">Nueva categoría</span>
                    <span class="dash-action__hint">Biblioteca educativa</span>
                </a>
            <?php endif; ?>
            <a class="dash-action" href="<?= url('/admin/contenidos') ?>">
                <span class="dash-action__label">Contenidos</span>
                <span class="dash-action__hint">Biblioteca</span>
            </a>
            <a class="dash-action dash-action--muted" href="<?= url('/') ?>">
                <span class="dash-action__label">Ver sitio público</span>
                <span class="dash-action__hint">Abrir portada</span>
            </a>
        </div>
    </div>
</section>
