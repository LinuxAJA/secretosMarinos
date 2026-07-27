<?php
/** Ficha de reporte ambiental (público resuelto, autor o staff). */
$tipoLabel = $types[$item['tipo']] ?? $item['tipo'];
$estadoLabel = $states[$item['estado']] ?? $item['estado'];
?>
<article class="section detail-view" aria-labelledby="report-title">
    <div class="container">
        <p class="article-view__meta"><a href="<?= url('/reportes') ?>">Reportes</a></p>

        <div class="detail-hero">
            <div>
                <p class="content-row__meta">
                    <span class="badge"><?= e($estadoLabel) ?></span>
                    · <?= e($tipoLabel) ?>
                </p>
                <h1 id="report-title" class="article-view__title"><?= e($item['titulo']) ?></h1>
                <p class="article-view__summary"><?= e($item['descripcion']) ?></p>

                <?php if ($canEdit): ?>
                    <div class="panel-actions">
                        <a class="btn btn--secondary" href="<?= url('/reportes/' . $item['id'] . '/editar') ?>">Editar</a>
                        <form method="post" action="<?= url('/reportes/' . $item['id'] . '/eliminar') ?>"
                              onsubmit="return confirm('¿Eliminar este reporte pendiente?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--danger">Eliminar</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($item['imagen'])): ?>
                <img class="detail-hero__image" src="<?= e(upload_url($item['imagen'])) ?>"
                     alt="Evidencia: <?= e($item['titulo']) ?>">
            <?php else: ?>
                <div class="detail-hero__placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        </div>

        <dl class="scientific-data">
            <?php if (!empty($item['ubicacion'])): ?>
                <div>
                    <dt>Ubicación</dt>
                    <dd><?= e($item['ubicacion']) ?></dd>
                </div>
            <?php endif; ?>
            <div>
                <dt>Reportado por</dt>
                <dd><?= e($item['autor_nombre'] ?? 'Usuario') ?></dd>
            </div>
            <div>
                <dt>Fecha</dt>
                <dd><?= e(format_date($item['creado_en'])) ?></dd>
            </div>
            <?php if (!empty($item['revisor_nombre'])): ?>
                <div>
                    <dt>Revisor</dt>
                    <dd><?= e($item['revisor_nombre']) ?></dd>
                </div>
            <?php endif; ?>
            <?php if (!empty($item['notas_revision']) && (can_review_reports() || ($item['estado'] ?? '') === 'resuelto' || can_edit_own_report($item) || (is_logged_in() && (int) ($item['usuario_id'] ?? 0) === (int) (current_user()['id'] ?? 0)))): ?>
                <div>
                    <dt>Notas de revisión</dt>
                    <dd><?= nl2br(e($item['notas_revision'])) ?></dd>
                </div>
            <?php endif; ?>
        </dl>
    </div>
</article>
