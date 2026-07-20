<?php
/**
 * Biblioteca educativa — listado público.
 * Vars: $items, $categories, $filters, $pagination
 */
$filters = $filters ?? ['categoria' => '', 'q' => ''];
$pagination = $pagination ?? ['page' => 1, 'pages' => 1, 'total' => 0];
$nivelLabel = ['basico' => 'Básico', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado'];
?>
<section class="section" aria-labelledby="edu-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="edu-title" class="section__title">Biblioteca educativa</h1>
        <p class="section__lead">
            Artículos y guías sobre oceanografía, biodiversidad y conservación.
        </p>

        <form class="filter-bar" method="get" action="<?= url('/educacion') ?>">
            <div class="form-field">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>" placeholder="Ej. manglar, coral…">
            </div>
            <div class="form-field">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>" <?= ($filters['categoria'] === $cat['slug']) ? 'selected' : '' ?>>
                            <?= e($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </form>

        <?php if (!$items): ?>
            <p class="empty-state">No hay contenidos publicados con esos filtros.</p>
        <?php else: ?>
            <div class="content-list">
                <?php foreach ($items as $item): ?>
                    <article class="content-row">
                        <div>
                            <p class="content-row__meta">
                                <?= e($item['categoria_nombre'] ?? 'Sin categoría') ?>
                                · <?= e($nivelLabel[$item['nivel']] ?? $item['nivel']) ?>
                            </p>
                            <h2 class="content-row__title">
                                <a href="<?= url('/educacion/' . $item['slug']) ?>"><?= e($item['titulo']) ?></a>
                            </h2>
                            <p class="content-row__excerpt"><?= e($item['resumen'] ?: excerpt($item['cuerpo'])) ?></p>
                        </div>
                        <a class="btn btn--secondary" href="<?= url('/educacion/' . $item['slug']) ?>">Leer</a>
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
                           href="<?= url('/educacion' . ($qs ? '?' . $qs : '')) ?>"><?= $p ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
