<?php
/**
 * Biblioteca educativa — depth-tick por nivel + cabecera foto.
 */
$filters = $filters ?? ['categoria' => '', 'q' => ''];
$pagination = $pagination ?? ['page' => 1, 'pages' => 1, 'total' => 0];
$nivelLabel = ['basico' => 'Básico', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado'];

$bandTitle = 'Biblioteca educativa';
$bandLead = 'Artículos y guías sobre oceanografía, biodiversidad y conservación.';
$bandImage = asset('img/module-educacion.jpg');
$bandKicker = APP_NAME;
require VIEWS_PATH . '/partials/page-band.php';
?>
<section class="section section--mist section--flush-top" aria-label="Contenidos educativos">
    <div class="container">
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
                <?php foreach ($items as $i => $item): ?>
                    <?php $nivel = $item['nivel'] ?? 'basico'; ?>
                    <article class="content-row" data-depth="<?= e($nivel) ?>">
                        <div class="content-row__rail" aria-hidden="true">
                            <span class="content-row__index"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <div class="depth-tick">
                                <span class="depth-tick__mark depth-tick__mark--<?= e($nivel) ?>"></span>
                                <span class="depth-tick__label"><?= e($nivelLabel[$nivel] ?? $nivel) ?></span>
                            </div>
                        </div>
                        <div class="content-row__main">
                            <p class="content-row__meta">
                                <span class="content-row__chip"><?= e($item['categoria_nombre'] ?? 'Sin categoría') ?></span>
                                <span><?= e($nivelLabel[$nivel] ?? $nivel) ?></span>
                            </p>
                            <h2 class="content-row__title">
                                <a href="<?= url('/educacion/' . $item['slug']) ?>"><?= e($item['titulo']) ?></a>
                            </h2>
                            <p class="content-row__excerpt"><?= e($item['resumen'] ?: excerpt($item['cuerpo'])) ?></p>
                        </div>
                        <a class="content-row__cta" href="<?= url('/educacion/' . $item['slug']) ?>">Leer</a>
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
