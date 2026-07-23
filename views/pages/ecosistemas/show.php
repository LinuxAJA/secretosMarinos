<?php /** Ficha pública de ecosistema. */ ?>
<article class="section detail-view" aria-labelledby="ecosystem-title">
    <div class="container">
        <p class="article-view__meta"><a href="<?= url('/ecosistemas') ?>">Ecosistemas</a></p>

        <div class="detail-hero">
            <div>
                <h1 id="ecosystem-title" class="article-view__title"><?= e($item['nombre']) ?></h1>
                <p class="article-view__summary"><?= e($item['descripcion']) ?></p>
            </div>
            <?php if (!empty($item['imagen'])): ?>
                <img class="detail-hero__image" src="<?= e(upload_url($item['imagen'])) ?>"
                     alt="Vista del ecosistema <?= e($item['nombre']) ?>">
            <?php else: ?>
                <div class="detail-hero__placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        </div>

        <div class="detail-sections">
            <?php if (!empty($item['funcion_ecologica'])): ?>
                <section>
                    <h2>Función ecológica</h2>
                    <p><?= nl2br(e($item['funcion_ecologica'])) ?></p>
                </section>
            <?php endif; ?>
            <?php if (!empty($item['amenazas'])): ?>
                <section>
                    <h2>Amenazas</h2>
                    <p><?= nl2br(e($item['amenazas'])) ?></p>
                </section>
            <?php endif; ?>
            <?php if (!empty($item['buenas_practicas'])): ?>
                <section>
                    <h2>Buenas prácticas</h2>
                    <p><?= nl2br(e($item['buenas_practicas'])) ?></p>
                </section>
            <?php endif; ?>
        </div>

        <section class="related-section" aria-labelledby="related-species">
            <h2 id="related-species">Especies asociadas</h2>
            <?php if (!$species): ?>
                <p class="empty-state">Aún no hay especies publicadas asociadas.</p>
            <?php else: ?>
                <div class="catalog-grid">
                    <?php foreach ($species as $specimen): ?>
                        <article class="catalog-card catalog-card--compact">
                            <div class="catalog-card__body">
                                <h3 class="catalog-card__title">
                                    <a href="<?= url('/especies/' . $specimen['slug']) ?>">
                                        <?= e($specimen['nombre_comun']) ?>
                                    </a>
                                </h3>
                                <p class="scientific-name"><?= e($specimen['nombre_cientifico']) ?></p>
                                <p><?= e(excerpt($specimen['descripcion'], 110)) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</article>
