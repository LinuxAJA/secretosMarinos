<?php
/** Catálogo público de especies. */
$filters ??= ['q' => '', 'ecosistema' => 0, 'conservacion' => ''];
$pagination ??= ['page' => 1, 'pages' => 1, 'total' => 0];
?>
<section class="section" aria-labelledby="species-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="species-title" class="section__title">Especies marinas</h1>
        <p class="section__lead">
            Consulta fichas científicas sobre biodiversidad, hábitat, distribución y conservación.
        </p>

        <form class="filter-bar filter-bar--species" method="get" action="<?= url('/especies') ?>">
            <div class="form-field">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>"
                       placeholder="Nombre común o científico">
            </div>
            <div class="form-field">
                <label for="ecosistema">Ecosistema</label>
                <select id="ecosistema" name="ecosistema">
                    <option value="">Todos</option>
                    <?php foreach ($ecosystems as $ecosystem): ?>
                        <option value="<?= (int) $ecosystem['id'] ?>"
                            <?= (int) $filters['ecosistema'] === (int) $ecosystem['id'] ? 'selected' : '' ?>>
                            <?= e($ecosystem['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="conservacion">Conservación</label>
                <select id="conservacion" name="conservacion">
                    <option value="">Todos</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?= e($state) ?>"
                            <?= $filters['conservacion'] === $state ? 'selected' : '' ?>>
                            <?= e($state) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </form>

        <?php if (!$items): ?>
            <p class="empty-state">No se encontraron especies con esos filtros.</p>
        <?php else: ?>
            <div class="catalog-grid">
                <?php foreach ($items as $item): ?>
                    <article class="catalog-card">
                        <?php if (!empty($item['imagen'])): ?>
                            <img class="catalog-card__image" src="<?= e(upload_url($item['imagen'])) ?>"
                                 alt="<?= e($item['nombre_comun']) ?>">
                        <?php else: ?>
                            <div class="catalog-card__placeholder" aria-hidden="true"></div>
                        <?php endif; ?>
                        <div class="catalog-card__body">
                            <p class="content-row__meta"><?= e($item['ecosistema_nombre'] ?? 'Sin ecosistema') ?></p>
                            <h2 class="catalog-card__title">
                                <a href="<?= url('/especies/' . $item['slug']) ?>"><?= e($item['nombre_comun']) ?></a>
                            </h2>
                            <p class="scientific-name"><?= e($item['nombre_cientifico']) ?></p>
                            <?php if (!empty($item['estado_conservacion'])): ?>
                                <span class="badge"><?= e($item['estado_conservacion']) ?></span>
                            <?php endif; ?>
                            <p><?= e(excerpt($item['descripcion'], 130)) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($pagination['pages'] > 1): ?>
            <nav class="pagination" aria-label="Paginación de especies">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <?php $qs = http_build_query(array_filter([
                        'q' => $filters['q'],
                        'ecosistema' => $filters['ecosistema'] ?: null,
                        'conservacion' => $filters['conservacion'],
                        'page' => $p > 1 ? $p : null,
                    ])); ?>
                    <a class="pagination__link <?= $p === $pagination['page'] ? 'is-active' : '' ?>"
                       href="<?= url('/especies' . ($qs ? '?' . $qs : '')) ?>"><?= $p ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
