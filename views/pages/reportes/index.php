<?php
/**
 * Reportes: CTAs → mis reportes (seguimiento) → casos resueltos (impacto).
 */
$filters ??= ['q' => '', 'tipo' => ''];
$pagination ??= ['page' => 1, 'pages' => 1, 'total' => 0];
$myReports ??= [];

$bandTitle = 'Reportes ambientales';
$bandLead = 'Canal de participación ciudadana para evidenciar problemas y darles seguimiento.';
$bandImage = asset('img/module-accion.jpg');
require VIEWS_PATH . '/partials/page-band.php';
?>
<section class="section section--mist section--flush-top" aria-label="Reportes ambientales">
    <div class="container">
        <div class="panel-actions" style="margin-bottom: var(--space-5)">
            <?php if (is_logged_in()): ?>
                <a class="btn btn--sol" href="<?= url('/reportes/crear') ?>">Crear reporte</a>
            <?php else: ?>
                <a class="btn btn--sol" href="<?= url('/login') ?>">Inicia sesión para reportar</a>
            <?php endif; ?>
            <?php if (can_review_reports()): ?>
                <a class="btn btn--secondary" href="<?= url('/admin/reportes') ?>">Cola de revisión</a>
            <?php endif; ?>
        </div>

        <?php if ($myReports): ?>
            <section class="related-section reports-mine" aria-labelledby="my-reports-title">
                <p class="panel-kicker">Tu seguimiento</p>
                <h2 id="my-reports-title" class="section__title" style="font-size:1.5rem;margin-bottom:1rem">
                    Mis reportes
                </h2>
                <div class="content-list">
                    <?php foreach ($myReports as $i => $mine): ?>
                        <article class="content-row">
                            <div class="content-row__rail" aria-hidden="true">
                                <span class="content-row__index"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <div class="content-row__main">
                                <p class="content-row__meta">
                                    <span class="content-row__chip"><?= e($states[$mine['estado']] ?? $mine['estado']) ?></span>
                                    <span><?= e($types[$mine['tipo']] ?? $mine['tipo']) ?></span>
                                </p>
                                <h3 class="content-row__title">
                                    <a href="<?= url('/reportes/' . $mine['id']) ?>"><?= e($mine['titulo']) ?></a>
                                </h3>
                            </div>
                            <a class="content-row__cta" href="<?= url('/reportes/' . $mine['id']) ?>">Ver</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <header class="reports-resolved__head<?= $myReports ? ' reports-resolved__head--after-mine' : '' ?>">
            <div>
                <p class="panel-kicker">Impacto ciudadano</p>
                <h2 class="section__title" style="margin-bottom:0.35rem">Casos resueltos</h2>
                <p class="section__lead" style="margin-bottom:0">
                    Hallazgos atendidos por la comunidad formativa.
                </p>
            </div>
        </header>

        <form class="filter-bar" method="get" action="<?= url('/reportes') ?>">
            <div class="form-field">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>"
                       placeholder="Título, descripción o ubicación">
            </div>
            <div class="form-field">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <?php foreach ($types as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $filters['tipo'] === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </form>

        <?php if (!$items): ?>
            <p class="empty-state">Aún no hay reportes resueltos publicados.</p>
        <?php else: ?>
            <div class="featured-list featured-list--reports" aria-label="Reportes resueltos">
                <?php foreach ($items as $i => $item): ?>
                    <a
                        class="featured-item <?= $i === 0 ? 'featured-item--lead' : '' ?>"
                        href="<?= url('/reportes/' . $item['id']) ?>"
                    >
                        <span
                            class="featured-item__media"
                            style="--feat-image: url('<?= e(!empty($item['imagen']) ? upload_url($item['imagen']) : asset('img/module-accion.jpg')) ?>')"
                            aria-hidden="true"
                        ></span>
                        <span class="featured-item__body">
                            <span class="badge badge--ok">Resuelto</span>
                            <span class="featured-item__type">
                                <?= e($types[$item['tipo']] ?? $item['tipo']) ?>
                            </span>
                            <strong><?= e($item['titulo']) ?></strong>
                            <?php if (!empty($item['ubicacion'])): ?>
                                <span class="featured-item__place"><?= e($item['ubicacion']) ?></span>
                            <?php endif; ?>
                            <span class="featured-item__excerpt"><?= e(excerpt($item['descripcion'], 110)) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($pagination['pages'] > 1): ?>
            <nav class="pagination" aria-label="Paginación de reportes">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <?php $qs = http_build_query(array_filter([
                        'q' => $filters['q'],
                        'tipo' => $filters['tipo'],
                        'page' => $p > 1 ? $p : null,
                    ])); ?>
                    <a class="pagination__link <?= $p === $pagination['page'] ? 'is-active' : '' ?>"
                       href="<?= url('/reportes' . ($qs ? '?' . $qs : '')) ?>"><?= $p ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
