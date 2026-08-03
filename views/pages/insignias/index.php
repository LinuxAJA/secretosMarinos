<?php
/** Catálogo público de insignias. */
$ownedIds = $ownedIds ?? [];
?>
<section class="section" aria-labelledby="badges-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="badges-title" class="section__title">Insignias</h1>
        <p class="section__lead">
            Logros que se desbloquean al acumular puntos ecológicos con tu participación.
        </p>

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
                <a class="btn btn--primary" href="<?= url('/panel') ?>">Mi progreso</a>
            <?php endif; ?>
        </p>
    </div>
</section>
