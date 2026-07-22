<?php /** Ficha pública de especie marina. */ ?>
<article class="section detail-view" aria-labelledby="species-detail-title">
    <div class="container">
        <p class="article-view__meta">
            <a href="<?= url('/especies') ?>">Especies</a>
            <?php if (!empty($item['ecosistema_slug'])): ?>
                · <a href="<?= url('/ecosistemas/' . $item['ecosistema_slug']) ?>">
                    <?= e($item['ecosistema_nombre']) ?>
                </a>
            <?php endif; ?>
        </p>

        <div class="detail-hero">
            <div>
                <h1 id="species-detail-title" class="article-view__title"><?= e($item['nombre_comun']) ?></h1>
                <p class="scientific-name scientific-name--large"><?= e($item['nombre_cientifico']) ?></p>
                <?php if (!empty($item['estado_conservacion'])): ?>
                    <span class="badge"><?= e($item['estado_conservacion']) ?></span>
                <?php endif; ?>
                <p class="article-view__summary"><?= e($item['descripcion']) ?></p>
            </div>
            <?php if (!empty($item['imagen'])): ?>
                <img class="detail-hero__image" src="<?= e(upload_url($item['imagen'])) ?>"
                     alt="<?= e($item['nombre_comun']) ?>">
            <?php else: ?>
                <div class="detail-hero__placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        </div>

        <dl class="scientific-data">
            <?php foreach ([
                'Clasificación' => $item['clasificacion'],
                'Hábitat' => $item['habitat'],
                'Distribución' => $item['distribucion'],
                'Amenazas' => $item['amenazas'],
                'Estado de conservación' => $item['estado_conservacion'],
                'Autor de la ficha' => $item['autor_nombre'],
            ] as $label => $value): ?>
                <?php if (!empty($value)): ?>
                    <div>
                        <dt><?= e($label) ?></dt>
                        <dd><?= nl2br(e($value)) ?></dd>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </dl>
    </div>
</article>
