<?php
/**
 * Estadísticas — paneles por dominio (Paso 8 · contraste + jerarquía)
 */
$edu = $stats['educacion'] ?? [];
$cat = $stats['catalogo'] ?? [];
$part = $stats['participacion'] ?? [];
$game = $stats['gamificacion'] ?? [];
$comunidad = $stats['comunidad'] ?? null;

$camp = $part['campanias'] ?? [];
$rep = $part['reportes'] ?? [];
$campDenom = max(1, (int) ($part['campanias_total'] ?? 0));
$repDenom = max(1, (int) ($part['reportes_total'] ?? 0));
$userDenom = max(1, (int) ($comunidad['usuarios_total'] ?? 1));
?>
<section class="stats" aria-label="Estadísticas operativas">
    <header class="dash-intro">
        <div class="dash-intro__copy">
            <p class="dash-intro__kicker">Métricas en vivo</p>
            <h2 class="dash-intro__title">Estadísticas de la plataforma</h2>
            <p class="dash-intro__lead">
                KPIs calculados al momento desde la base de datos.
                <?php if (is_docente() && !is_admin()): ?>
                    Como docente ves participación y catálogo; las métricas de cuentas son solo del administrador.
                <?php endif; ?>
            </p>
        </div>
        <a class="btn btn--secondary" href="<?= url('/admin') ?>">← Dashboard</a>
    </header>

    <?php if (is_array($comunidad)): ?>
        <article class="stats-panel">
            <header class="stats-panel__head">
                <div>
                    <p class="stats-panel__kicker">01 · Comunidad</p>
                    <h3 class="stats-panel__title">Usuarios y roles</h3>
                </div>
                <a class="stats-panel__link" href="<?= url('/admin/usuarios') ?>">Gestionar usuarios →</a>
            </header>

            <div class="dash-mosaic dash-mosaic--3">
                <div class="dash-kpi">
                    <span class="dash-kpi__label">Usuarios totales</span>
                    <span class="dash-kpi__value"><?= (int) ($comunidad['usuarios_total'] ?? 0) ?></span>
                </div>
                <div class="dash-kpi">
                    <span class="dash-kpi__label">Activos</span>
                    <span class="dash-kpi__value"><?= (int) ($comunidad['usuarios_activos'] ?? 0) ?></span>
                </div>
                <div class="dash-kpi dash-kpi--sol">
                    <span class="dash-kpi__label">Inactivos</span>
                    <span class="dash-kpi__value"><?= (int) ($comunidad['usuarios_inactivos'] ?? 0) ?></span>
                </div>
            </div>

            <div class="stats-chart">
                <h4 class="stats-chart__title">Distribución por rol</h4>
                <div class="stats-bars">
                    <?php foreach (($comunidad['por_rol'] ?? []) as $rol => $total): ?>
                        <?php $pct = (int) round(((int) $total / $userDenom) * 100); ?>
                        <div class="stats-bar">
                            <div class="stats-bar__meta">
                                <span><?= e(ucfirst((string) $rol)) ?></span>
                                <strong><?= (int) $total ?> <em>(<?= $pct ?>%)</em></strong>
                            </div>
                            <div class="stats-bar__track" aria-hidden="true">
                                <span class="stats-bar__fill" style="width:<?= $pct ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>
    <?php endif; ?>

    <article class="stats-panel">
        <header class="stats-panel__head">
            <div>
                <p class="stats-panel__kicker">02 · Biblioteca</p>
                <h3 class="stats-panel__title">Educación y noticias</h3>
            </div>
            <div class="stats-panel__links">
                <a class="stats-panel__link" href="<?= url('/admin/contenidos') ?>">Contenidos →</a>
                <a class="stats-panel__link" href="<?= url('/admin/noticias') ?>">Noticias →</a>
            </div>
        </header>
        <div class="dash-mosaic dash-mosaic--2">
            <a class="dash-kpi" href="<?= url('/admin/contenidos') ?>">
                <span class="dash-kpi__label">Contenidos</span>
                <span class="dash-kpi__value"><?= (int) ($edu['contenidos_total'] ?? 0) ?></span>
                <span class="dash-kpi__meta"><?= (int) ($edu['contenidos_publicados'] ?? 0) ?> publicados</span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <a class="dash-kpi" href="<?= url('/admin/noticias') ?>">
                <span class="dash-kpi__label">Noticias</span>
                <span class="dash-kpi__value"><?= (int) ($edu['noticias_total'] ?? 0) ?></span>
                <span class="dash-kpi__meta"><?= (int) ($edu['noticias_publicadas'] ?? 0) ?> publicadas</span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
        </div>
    </article>

    <article class="stats-panel">
        <header class="stats-panel__head">
            <div>
                <p class="stats-panel__kicker">03 · Catálogo</p>
                <h3 class="stats-panel__title">Ciencia marina</h3>
            </div>
            <div class="stats-panel__links">
                <a class="stats-panel__link" href="<?= url('/admin/ecosistemas') ?>">Ecosistemas →</a>
                <a class="stats-panel__link" href="<?= url('/admin/especies') ?>">Especies →</a>
            </div>
        </header>
        <div class="dash-mosaic dash-mosaic--2">
            <a class="dash-kpi" href="<?= url('/admin/ecosistemas') ?>">
                <span class="dash-kpi__label">Ecosistemas</span>
                <span class="dash-kpi__value"><?= (int) ($cat['ecosistemas_total'] ?? 0) ?></span>
                <span class="dash-kpi__meta"><?= (int) ($cat['ecosistemas_publicados'] ?? 0) ?> publicados</span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <a class="dash-kpi" href="<?= url('/admin/especies') ?>">
                <span class="dash-kpi__label">Especies</span>
                <span class="dash-kpi__value"><?= (int) ($cat['especies_total'] ?? 0) ?></span>
                <span class="dash-kpi__meta"><?= (int) ($cat['especies_publicadas'] ?? 0) ?> publicadas</span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
        </div>
    </article>

    <article class="stats-panel">
        <header class="stats-panel__head">
            <div>
                <p class="stats-panel__kicker">04 · Participación</p>
                <h3 class="stats-panel__title">Campañas y reportes</h3>
            </div>
            <div class="stats-panel__links">
                <a class="stats-panel__link" href="<?= url('/admin/campanias') ?>">Campañas →</a>
                <a class="stats-panel__link" href="<?= url('/admin/reportes') ?>">Reportes →</a>
            </div>
        </header>

        <div class="dash-mosaic dash-mosaic--2">
            <a class="dash-kpi" href="<?= url('/admin/campanias') ?>">
                <span class="dash-kpi__label">Campañas</span>
                <span class="dash-kpi__value"><?= (int) ($part['campanias_total'] ?? 0) ?></span>
                <span class="dash-kpi__go">Administrar →</span>
            </a>
            <a class="dash-kpi dash-kpi--sol" href="<?= url('/admin/reportes') ?>">
                <span class="dash-kpi__label">Reportes</span>
                <span class="dash-kpi__value"><?= (int) ($part['reportes_total'] ?? 0) ?></span>
                <span class="dash-kpi__meta"><?= (int) ($rep['pendiente'] ?? 0) ?> pendientes</span>
                <span class="dash-kpi__go">Revisar cola →</span>
            </a>
        </div>

        <div class="stats-charts">
            <div class="stats-chart">
                <h4 class="stats-chart__title">Campañas por estado</h4>
                <div class="stats-bars">
                    <?php
                    $campRows = [
                        'activa' => ['Activas', 'bio'],
                        'finalizada' => ['Finalizadas', 'muted'],
                        'cancelada' => ['Canceladas', 'warn'],
                    ];
                    foreach ($campRows as $key => [$label, $tone]):
                        $n = (int) ($camp[$key] ?? 0);
                        $pct = (int) round(($n / $campDenom) * 100);
                    ?>
                        <div class="stats-bar">
                            <div class="stats-bar__meta">
                                <span><?= e($label) ?></span>
                                <strong><?= $n ?> <em>(<?= $pct ?>%)</em></strong>
                            </div>
                            <div class="stats-bar__track" aria-hidden="true">
                                <span class="stats-bar__fill stats-bar__fill--<?= e($tone) ?>" style="width:<?= $pct ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="stats-chart">
                <h4 class="stats-chart__title">Reportes por estado</h4>
                <div class="stats-bars">
                    <?php
                    $repRows = [
                        'pendiente' => ['Pendientes', 'warn'],
                        'en_revision' => ['En revisión', 'sol'],
                        'resuelto' => ['Resueltos', 'bio'],
                    ];
                    foreach ($repRows as $key => [$label, $tone]):
                        $n = (int) ($rep[$key] ?? 0);
                        $pct = (int) round(($n / $repDenom) * 100);
                    ?>
                        <div class="stats-bar">
                            <div class="stats-bar__meta">
                                <span><?= e($label) ?></span>
                                <strong><?= $n ?> <em>(<?= $pct ?>%)</em></strong>
                            </div>
                            <div class="stats-bar__track" aria-hidden="true">
                                <span class="stats-bar__fill stats-bar__fill--<?= e($tone) ?>" style="width:<?= $pct ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </article>

    <article class="stats-panel">
        <header class="stats-panel__head">
            <div>
                <p class="stats-panel__kicker">05 · Gamificación</p>
                <h3 class="stats-panel__title">Insignias, puntos y ranking</h3>
            </div>
            <div class="stats-panel__links">
                <a class="stats-panel__link" href="<?= url('/admin/insignias') ?>">Insignias →</a>
                <?php if (can_adjust_points()): ?>
                    <a class="stats-panel__link" href="<?= url('/admin/puntos') ?>">Ajustar puntos →</a>
                <?php endif; ?>
                <a class="stats-panel__link" href="<?= url('/ranking') ?>">Ver ranking público →</a>
            </div>
        </header>

        <div class="dash-mosaic dash-mosaic--3">
            <div class="dash-kpi">
                <span class="dash-kpi__label">Insignias en catálogo</span>
                <span class="dash-kpi__value"><?= (int) ($game['insignias_catalogo'] ?? 0) ?></span>
            </div>
            <div class="dash-kpi">
                <span class="dash-kpi__label">Insignias otorgadas</span>
                <span class="dash-kpi__value"><?= (int) ($game['insignias_otorgadas'] ?? 0) ?></span>
            </div>
            <div class="dash-kpi dash-kpi--sol">
                <span class="dash-kpi__label">Promedio puntos</span>
                <span class="dash-kpi__value"><?= e((string) ($game['promedio_puntos_activos'] ?? 0)) ?></span>
                <span class="dash-kpi__meta">Usuarios activos</span>
            </div>
        </div>

        <?php $top = $game['ranking_top'] ?? []; ?>
        <?php if ($top): ?>
            <div class="stats-chart">
                <h4 class="stats-chart__title">Top 5 ranking</h4>
                <div class="table-wrap table-wrap--dark">
                    <table class="data-table data-table--on-dark">
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
                                    <td><strong><?= e($row['nombre'] ?? '') ?></strong></td>
                                    <td><?= e($row['rol'] ?? '') ?></td>
                                    <td><?= (int) ($row['puntos'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="stats-empty">Aún no hay usuarios con puntos en el ranking.</p>
        <?php endif; ?>
    </article>
</section>
