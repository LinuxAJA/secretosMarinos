<?php
/** Catálogo público de insignias. */
$ownedIds = $ownedIds ?? [];

$bandTitle = 'Insignias';
$bandLead = 'Logros que se desbloquean al acumular puntos ecológicos con tu participación.';
$bandImage = asset('img/module-accion.jpg');
require VIEWS_PATH . '/partials/page-band.php';
?>
<section class="section section--mist section--flush-top" aria-label="Catálogo de insignias">
    <div class="container">
        <?php if (!$items): ?>
            <p class="empty-state">Aún no hay insignias activas.</p>
        <?php else: ?>
            <div class="catalog-grid">
                <?php foreach ($items as $item): ?>
                    <?php $owned = in_array((int) $item['id'], $ownedIds, true); ?>
                    <article class="catalog-card catalog-card--compact <?= $owned ? 'badge-card--owned' : '' ?>">
                        <div class="catalog-card__body">
                            <div class="badge-icon badge-icon--<?= e($item['icono'] ?: 'default') ?>" aria-hidden="true"></div>
                            <p class="content-row__meta">
                                <?= (int) $item['puntos_requeridos'] ?> puntos
                                <?php if ($owned): ?>
                                    · <span class="badge badge--ok">Obtenida</span>
                                <?php endif; ?>
                            </p>
                            <h2 class="catalog-card__title"><?= e($item['nombre']) ?></h2>
                            <p><?= e($item['descripcion']) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="panel-actions" style="margin-top: var(--space-5)">
            <a class="btn btn--secondary" href="<?= url('/ranking') ?>">Ver ranking</a>
            <?php if (is_logged_in()): ?>
                <a class="btn btn--sol" href="<?= url('/panel') ?>">Mi progreso</a>
            <?php endif; ?>
        </p>
    </div>
</section>
