<?php
/**
 * Reportes ambientales: resueltos públicos + mis reportes (si hay sesión).
 */
$filters ??= ['q' => '', 'tipo' => ''];
$pagination ??= ['page' => 1, 'pages' => 1, 'total' => 0];
$myReports ??= [];
?>
<section class="section" aria-labelledby="reports-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="reports-title" class="section__title">Reportes ambientales</h1>
        <p class="section__lead">
            Canal de participación ciudadana para evidenciar problemas y darles seguimiento.
        </p>

        <div class="panel-actions" style="margin-bottom: var(--space-4)">
            <?php if (is_logged_in()): ?>
                <a class="btn btn--primary" href="<?= url('/reportes/crear') ?>">Crear reporte</a>
            <?php else: ?>
                <a class="btn btn--primary" href="<?= url('/login') ?>">Inicia sesión para reportar</a>
            <?php endif; ?>
            <?php if (can_review_reports()): ?>
                <a class="btn btn--secondary" href="<?= url('/admin/reportes') ?>">Cola de revisión</a>
            <?php endif; ?>
        </div>

        <?php if ($myReports): ?>
            <section class="related-section" aria-labelledby="my-reports-title">
                <h2 id="my-reports-title">Mis reportes</h2>
                <div class="content-list">
                    <?php foreach ($myReports as $mine): ?>
                        <article class="content-row">
                            <div>
                                <p class="content-row__meta">
                                    <span class="badge"><?= e($states[$mine['estado']] ?? $mine['estado']) ?></span>
                                    · <?= e($types[$mine['tipo']] ?? $mine['tipo']) ?>
                                </p>
                                <h3 class="content-row__title">
                                    <a href="<?= url('/reportes/' . $mine['id']) ?>"><?= e($mine['titulo']) ?></a>
                                </h3>
                            </div>
                            <a href="<?= url('/reportes/' . $mine['id']) ?>">Ver</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <h2 class="section__title" style="font-size:1.5rem;margin-top:var(--space-5)">Casos resueltos</h2>

        <form class="filter-bar filter-bar--species" method="get" action="<?= url('/reportes') ?>">
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
            <div class="catalog-grid">
                <?php foreach ($items as $item): ?>
                    <article class="catalog-card catalog-card--compact">
                        <div class="catalog-card__body">
                            <p class="content-row__meta"><?= e($types[$item['tipo']] ?? $item['tipo']) ?></p>
                            <h3 class="catalog-card__title">
                                <a href="<?= url('/reportes/' . $item['id']) ?>"><?= e($item['titulo']) ?></a>
                            </h3>
                            <?php if (!empty($item['ubicacion'])): ?>
                                <p class="muted"><?= e($item['ubicacion']) ?></p>
                            <?php endif; ?>
                            <p><?= e(excerpt($item['descripcion'], 120)) ?></p>
                        </div>
                    </article>
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
