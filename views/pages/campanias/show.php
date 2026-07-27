<?php
/** Ficha pública de campaña ambiental. */
$estadoLabel = $allStates[$item['estado']] ?? $item['estado'];
?>
<article class="section detail-view" aria-labelledby="campaign-title">
    <div class="container">
        <p class="article-view__meta"><a href="<?= url('/campanias') ?>">Campañas</a></p>

        <div class="detail-hero">
            <div>
                <p class="content-row__meta">
                    <span class="badge"><?= e($estadoLabel) ?></span>
                    <?php if (!empty($item['responsable_nombre'])): ?>
                        · Responsable: <?= e($item['responsable_nombre']) ?>
                    <?php endif; ?>
                </p>
                <h1 id="campaign-title" class="article-view__title"><?= e($item['titulo']) ?></h1>
                <p class="article-view__summary"><?= e($item['descripcion']) ?></p>
            </div>
            <?php if (!empty($item['imagen'])): ?>
                <img class="detail-hero__image" src="<?= e(upload_url($item['imagen'])) ?>"
                     alt="Campaña <?= e($item['titulo']) ?>">
            <?php else: ?>
                <div class="detail-hero__placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        </div>

        <dl class="scientific-data">
            <?php if (!empty($item['objetivo'])): ?>
                <div>
                    <dt>Objetivo</dt>
                    <dd><?= nl2br(e($item['objetivo'])) ?></dd>
                </div>
            <?php endif; ?>
            <?php if (!empty($item['fecha_inicio'])): ?>
                <div>
                    <dt>Fecha de inicio</dt>
                    <dd><?= e(format_date($item['fecha_inicio'])) ?></dd>
                </div>
            <?php endif; ?>
            <?php if (!empty($item['fecha_fin'])): ?>
                <div>
                    <dt>Fecha de fin</dt>
                    <dd><?= e(format_date($item['fecha_fin'])) ?></dd>
                </div>
            <?php endif; ?>
        </dl>
    </div>
</article>
