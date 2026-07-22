<?php
/** Catálogo público de ecosistemas. */
$filters ??= ['q' => ''];
$pagination ??= ['page' => 1, 'pages' => 1, 'total' => 0];
?>
<section class="section" aria-labelledby="ecosystems-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="ecosystems-title" class="section__title">Ecosistemas marinos</h1>
        <p class="section__lead">
            Conoce su función ecológica, amenazas y las especies que dependen de ellos.
        </p>

        <form class="filter-bar filter-bar--simple" method="get" action="<?= url('/ecosistemas') ?>">
            <div class="form-field">
                <label for="q">Buscar ecosistema</label>
                <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>"
                       placeholder="Ej. manglar, arrecife…">
            </div>
            <button type="submit" class="btn btn--primary">Buscar</button>
        </form>

        <?php if (!$items): ?>
            <p class="empty-state">No hay ecosistemas publicados con esa búsqueda.</p>
        <?php else: ?>
            <div class="catalog-grid">
                <?php foreach ($items as $item): ?>
                    <article class="catalog-card">
                        <?php if (!empty($item['imagen'])): ?>
                            <img class="catalog-card__image" src="<?= e(upload_url($item['imagen'])) ?>"
                                 alt="Ecosistema <?= e($item['nombre']) ?>">
                        <?php else: ?>
                            <div class="catalog-card__placeholder" aria-hidden="true"></div>
                        <?php endif; ?>
                        <div class="catalog-card__body">
                            <p class="content-row__meta"><?= (int) $item['total_especies'] ?> especies registradas</p>
                            <h2 class="catalog-card__title">
                                <a href="<?= url('/ecosistemas/' . $item['slug']) ?>"><?= e($item['nombre']) ?></a>
                            </h2>
                            <p><?= e(excerpt($item['descripcion'], 150)) ?></p>
                            <a href="<?= url('/ecosistemas/' . $item['slug']) ?>">Explorar ecosistema</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($pagination['pages'] > 1): ?>
            <nav class="pagination" aria-label="Paginación de ecosistemas">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <?php $qs = http_build_query(array_filter(['q' => $filters['q'], 'page' => $p > 1 ? $p : null])); ?>
                    <a class="pagination__link <?= $p === $pagination['page'] ? 'is-active' : '' ?>"
                       href="<?= url('/ecosistemas' . ($qs ? '?' . $qs : '')) ?>"><?= $p ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
