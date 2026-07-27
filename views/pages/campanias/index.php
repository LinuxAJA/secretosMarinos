<?php
/**
 * Catálogo público de campañas ambientales (activa / finalizada).
 */
$filters ??= ['q' => '', 'estado' => ''];
$pagination ??= ['page' => 1, 'pages' => 1, 'total' => 0];
$states ??= [];
?>
<section class="section" aria-labelledby="campaigns-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="campaigns-title" class="section__title">Campañas ambientales</h1>
        <p class="section__lead">
            Acciones colectivas de conservación y sensibilización lideradas por la comunidad formativa.
        </p>

        <form class="filter-bar filter-bar--species" method="get" action="<?= url('/campanias') ?>">
            <div class="form-field">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>"
                       placeholder="Título u objetivo">
            </div>
            <div class="form-field">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Activas y finalizadas</option>
                    <?php foreach ($states as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $filters['estado'] === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </form>

        <?php if (!$items): ?>
            <p class="empty-state">No hay campañas públicas con esos filtros.</p>
        <?php else: ?>
            <div class="catalog-grid">
                <?php foreach ($items as $item): ?>
                    <article class="catalog-card">
                        <?php if (!empty($item['imagen'])): ?>
                            <img class="catalog-card__image" src="<?= e(upload_url($item['imagen'])) ?>"
                                 alt="Campaña <?= e($item['titulo']) ?>">
                        <?php else: ?>
                            <div class="catalog-card__placeholder" aria-hidden="true"></div>
                        <?php endif; ?>
                        <div class="catalog-card__body">
                            <p class="content-row__meta">
                                <span class="badge"><?= e($states[$item['estado']] ?? $item['estado']) ?></span>
                                <?php if (!empty($item['responsable_nombre'])): ?>
                                    · <?= e($item['responsable_nombre']) ?>
                                <?php endif; ?>
                            </p>
                            <h2 class="catalog-card__title">
                                <a href="<?= url('/campanias/' . $item['slug']) ?>"><?= e($item['titulo']) ?></a>
                            </h2>
                            <p><?= e(excerpt($item['descripcion'], 140)) ?></p>
                            <?php if (!empty($item['fecha_inicio']) || !empty($item['fecha_fin'])): ?>
                                <p class="muted">
                                    <?= e(format_date($item['fecha_inicio'] ?? null)) ?>
                                    <?php if (!empty($item['fecha_fin'])): ?>
                                        — <?= e(format_date($item['fecha_fin'])) ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($pagination['pages'] > 1): ?>
            <nav class="pagination" aria-label="Paginación de campañas">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <?php $qs = http_build_query(array_filter([
                        'q' => $filters['q'],
                        'estado' => $filters['estado'],
                        'page' => $p > 1 ? $p : null,
                    ])); ?>
                    <a class="pagination__link <?= $p === $pagination['page'] ? 'is-active' : '' ?>"
                       href="<?= url('/campanias' . ($qs ? '?' . $qs : '')) ?>"><?= $p ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
