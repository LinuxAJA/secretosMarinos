<?php
/**
 * Estadísticas básicas — Paso 7.
 * Admin ve bloque Comunidad; docente no (Opción A / StatsService).
 */
$edu = $stats['educacion'] ?? [];
$cat = $stats['catalogo'] ?? [];
$part = $stats['participacion'] ?? [];
$game = $stats['gamificacion'] ?? [];
$comunidad = $stats['comunidad'] ?? null;

$camp = $part['campanias'] ?? [];
$rep = $part['reportes'] ?? [];
$campTotal = max(1, (int) ($part['campanias_total'] ?? 0));
$repTotal = max(1, (int) ($part['reportes_total'] ?? 0));
?>
<section class="admin-stats">
    <p class="section__lead">
        KPIs operativos calculados al momento desde la base de datos.
        <?php if (is_docente() && !is_admin()): ?>
            Como docente ves participación y catálogo; las métricas de cuentas son solo del administrador.
        <?php endif; ?>
    </p>

    <?php if (is_array($comunidad)): ?>
        <h2 class="admin-stats__title">Comunidad</h2>
        <div class="panel-grid">
            <article class="panel-stat">
                <h3 class="panel-stat__label">Usuarios totales</h3>
                <p class="panel-stat__value"><?= (int) ($comunidad['usuarios_total'] ?? 0) ?></p>
            </article>
            <article class="panel-stat">
                <h3 class="panel-stat__label">Activos</h3>
                <p class="panel-stat__value"><?= (int) ($comunidad['usuarios_activos'] ?? 0) ?></p>
            </article>
            <article class="panel-stat">
                <h3 class="panel-stat__label">Inactivos</h3>
                <p class="panel-stat__value"><?= (int) ($comunidad['usuarios_inactivos'] ?? 0) ?></p>
            </article>
        </div>
        <div class="stats-bars">
            <?php foreach (($comunidad['por_rol'] ?? []) as $rol => $total): ?>
                <?php
                $maxRole = max(1, (int) ($comunidad['usuarios_total'] ?? 1));
                $pct = (int) round(((int) $total / $maxRole) * 100);
                ?>
                <div class="stats-bar">
                    <div class="stats-bar__meta">
                        <span><?= e(ucfirst((string) $rol)) ?></span>
                        <strong><?= (int) $total ?></strong>
                    </div>
                    <div class="stats-bar__track" aria-hidden="true">
                        <span class="stats-bar__fill" style="width:<?= $pct ?>%"></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="form-hint"><a href="<?= url('/admin/usuarios') ?>">Gestionar usuarios →</a></p>
    <?php endif; ?>

    <h2 class="admin-stats__title">Educación</h2>
    <div class="panel-grid">
        <article class="panel-stat">
            <h3 class="panel-stat__label">Contenidos</h3>
            <p class="panel-stat__value"><?= (int) ($edu['contenidos_total'] ?? 0) ?></p>
            <p class="muted"><?= (int) ($edu['contenidos_publicados'] ?? 0) ?> publicados</p>
        </article>
        <article class="panel-stat">
            <h3 class="panel-stat__label">Noticias</h3>
            <p class="panel-stat__value"><?= (int) ($edu['noticias_total'] ?? 0) ?></p>
            <p class="muted"><?= (int) ($edu['noticias_publicadas'] ?? 0) ?> publicadas</p>
        </article>
    </div>

    <h2 class="admin-stats__title">Catálogo científico</h2>
    <div class="panel-grid">
        <article class="panel-stat">
            <h3 class="panel-stat__label">Ecosistemas</h3>
            <p class="panel-stat__value"><?= (int) ($cat['ecosistemas_total'] ?? 0) ?></p>
            <p class="muted"><?= (int) ($cat['ecosistemas_publicados'] ?? 0) ?> publicados</p>
        </article>
        <article class="panel-stat">
            <h3 class="panel-stat__label">Especies</h3>
            <p class="panel-stat__value"><?= (int) ($cat['especies_total'] ?? 0) ?></p>
            <p class="muted"><?= (int) ($cat['especies_publicadas'] ?? 0) ?> publicadas</p>
        </article>
    </div>

    <h2 class="admin-stats__title">Participación</h2>
    <div class="panel-grid">
        <article class="panel-stat">
            <h3 class="panel-stat__label">Campañas</h3>
            <p class="panel-stat__value"><?= (int) ($part['campanias_total'] ?? 0) ?></p>
        </article>
        <article class="panel-stat">
            <h3 class="panel-stat__label">Reportes</h3>
            <p class="panel-stat__value"><?= (int) ($part['reportes_total'] ?? 0) ?></p>
            <p class="muted"><?= (int) ($rep['pendiente'] ?? 0) ?> pendientes</p>
        </article>
    </div>

    <div class="stats-split">
        <div>
            <h3 class="admin-stats__subtitle">Campañas por estado</h3>
            <div class="stats-bars">
                <?php foreach (['activa' => 'Activas', 'finalizada' => 'Finalizadas', 'cancelada' => 'Canceladas'] as $key => $label): ?>
                    <?php
                    $n = (int) ($camp[$key] ?? 0);
                    $pct = (int) round(($n / $campTotal) * 100);
                    ?>
                    <div class="stats-bar">
                        <div class="stats-bar__meta">
                            <span><?= e($label) ?></span>
                            <strong><?= $n ?></strong>
                        </div>
                        <div class="stats-bar__track" aria-hidden="true">
                            <span class="stats-bar__fill" style="width:<?= $pct ?>%"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <h3 class="admin-stats__subtitle">Reportes por estado</h3>
            <div class="stats-bars">
                <?php foreach (['pendiente' => 'Pendientes', 'en_revision' => 'En revisión', 'resuelto' => 'Resueltos'] as $key => $label): ?>
                    <?php
                    $n = (int) ($rep[$key] ?? 0);
                    $pct = (int) round(($n / $repTotal) * 100);
                    ?>
                    <div class="stats-bar">
                        <div class="stats-bar__meta">
                            <span><?= e($label) ?></span>
                            <strong><?= $n ?></strong>
                        </div>
                        <div class="stats-bar__track" aria-hidden="true">
                            <span class="stats-bar__fill" style="width:<?= $pct ?>%"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <h2 class="admin-stats__title">Gamificación</h2>
    <div class="panel-grid">
        <article class="panel-stat">
            <h3 class="panel-stat__label">Insignias en catálogo</h3>
            <p class="panel-stat__value"><?= (int) ($game['insignias_catalogo'] ?? 0) ?></p>
        </article>
        <article class="panel-stat">
            <h3 class="panel-stat__label">Insignias otorgadas</h3>
            <p class="panel-stat__value"><?= (int) ($game['insignias_otorgadas'] ?? 0) ?></p>
        </article>
        <article class="panel-stat">
            <h3 class="panel-stat__label">Promedio puntos (activos)</h3>
            <p class="panel-stat__value"><?= e((string) ($game['promedio_puntos_activos'] ?? 0)) ?></p>
        </article>
    </div>

    <?php $top = $game['ranking_top'] ?? []; ?>
    <?php if ($top): ?>
        <h3 class="admin-stats__subtitle">Top 5 ranking</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($row['nombre'] ?? '') ?></td>
                            <td><?= e($row['rol'] ?? '') ?></td>
                            <td><?= (int) ($row['puntos'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty-state">Aún no hay usuarios con puntos en el ranking.</p>
    <?php endif; ?>
</section>
