<?php
/**
 * Listado público de noticias — spotlight tipográfico + filas.
 */
$filters = $filters ?? ['categoria' => '', 'q' => ''];
$pagination = $pagination ?? ['page' => 1, 'pages' => 1, 'total' => 0];
$featured = $featured ?? [];

$bandTitle = 'Noticias ambientales';
$bandLead = 'Novedades, campañas, descubrimientos y normativa relacionada con el océano.';
$bandImage = asset('img/module-noticias.jpg');
$bandKicker = APP_NAME;
require VIEWS_PATH . '/partials/page-band.php';
?>
<section class="section section--mist section--flush-top" aria-label="Listado de noticias">
    <div class="container">
        <?php if ($featured && empty($filters['q']) && empty($filters['categoria'])): ?>
            <div class="spotlight-list" aria-label="Destacadas">
                <?php foreach ($featured as $i => $feat): ?>
                    <a
                        class="spotlight-card <?= $i === 0 ? 'spotlight-card--lead' : '' ?>"
                        href="<?= url('/noticias/' . $feat['slug']) ?>"
                    >
                        <span class="spotlight-card__badge">Destacada</span>
                        <span class="spotlight-card__tag"><?= e($feat['categoria'] ?? 'General') ?></span>
                        <strong class="spotlight-card__title"><?= e($feat['titulo']) ?></strong>
                        <span class="spotlight-card__meta">
                            <?= e(format_date($feat['publicado_en'] ?? $feat['creado_en'] ?? null)) ?>
                        </span>
                        <span class="spotlight-card__excerpt">
                            <?= e($feat['resumen'] ?: excerpt($feat['cuerpo'], 120)) ?>
                        </span>
                        <span class="spotlight-card__go">Leer noticia →</span>
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
                <?php foreach ($items as $i => $item): ?>
                    <article class="content-row">
                        <div class="content-row__rail" aria-hidden="true">
                            <span class="content-row__index"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="content-row__main">
                            <p class="content-row__meta">
                                <span class="content-row__chip"><?= e($item['categoria'] ?? 'General') ?></span>
                                <span><?= e(format_date($item['publicado_en'] ?? $item['creado_en'])) ?></span>
                            </p>
                            <h2 class="content-row__title">
                                <a href="<?= url('/noticias/' . $item['slug']) ?>"><?= e($item['titulo']) ?></a>
                            </h2>
                            <p class="content-row__excerpt"><?= e($item['resumen'] ?: excerpt($item['cuerpo'])) ?></p>
                        </div>
                        <a class="content-row__cta" href="<?= url('/noticias/' . $item['slug']) ?>">Leer</a>
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
