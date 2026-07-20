<?php
/**
 * Listado público de noticias.
 */
$filters = $filters ?? ['categoria' => '', 'q' => ''];
$pagination = $pagination ?? ['page' => 1, 'pages' => 1, 'total' => 0];
$featured = $featured ?? [];
?>
<section class="section" aria-labelledby="news-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="news-title" class="section__title">Noticias ambientales</h1>
        <p class="section__lead">
            Novedades, campañas, descubrimientos y normativa relacionada con el océano.
        </p>

        <?php if ($featured && empty($filters['q']) && empty($filters['categoria'])): ?>
            <div class="featured-list" aria-label="Destacadas">
                <?php foreach ($featured as $feat): ?>
                    <a class="featured-item" href="<?= url('/noticias/' . $feat['slug']) ?>">
                        <span class="badge">Destacada</span>
                        <strong><?= e($feat['titulo']) ?></strong>
                        <span><?= e($feat['resumen'] ?: excerpt($feat['cuerpo'], 100)) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="filter-bar" method="get" action="<?= url('/noticias') ?>">
            <div class="form-field">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>">
            </div>
            <div class="form-field">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= ($filters['categoria'] === $cat) ? 'selected' : '' ?>>
                            <?= e($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </form>

        <?php if (!$items): ?>
            <p class="empty-state">No hay noticias publicadas con esos filtros.</p>
        <?php else: ?>
            <div class="content-list">
                <?php foreach ($items as $item): ?>
                    <article class="content-row">
                        <div>
                            <p class="content-row__meta">
                                <?= e($item['categoria'] ?? 'General') ?>
                                · <?= e(format_date($item['publicado_en'] ?? $item['creado_en'])) ?>
                            </p>
                            <h2 class="content-row__title">
                                <a href="<?= url('/noticias/' . $item['slug']) ?>"><?= e($item['titulo']) ?></a>
                            </h2>
                            <p class="content-row__excerpt"><?= e($item['resumen'] ?: excerpt($item['cuerpo'])) ?></p>
                        </div>
                        <a class="btn btn--secondary" href="<?= url('/noticias/' . $item['slug']) ?>">Leer</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($pagination['pages'] > 1): ?>
                <nav class="pagination" aria-label="Paginación">
                    <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                        <?php
                        $qs = http_build_query(array_filter([
                            'categoria' => $filters['categoria'] ?: null,
                            'q' => $filters['q'] ?: null,
                            'page' => $p > 1 ? $p : null,
                        ]));
                        ?>
                        <a class="pagination__link <?= $p === $pagination['page'] ? 'is-active' : '' ?>"
                           href="<?= url('/noticias' . ($qs ? '?' . $qs : '')) ?>"><?= $p ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
