<?php
/**
 * Lectura de una noticia.
 */
?>
<article class="section article-view" aria-labelledby="news-article-title">
    <div class="container article-view__inner">
        <p class="article-view__meta">
            <a href="<?= url('/noticias') ?>">Noticias</a>
            <?php if (!empty($item['categoria'])): ?>
                · <?= e($item['categoria']) ?>
            <?php endif; ?>
            <?php if (!empty($item['destacada'])): ?>
                · Destacada
            <?php endif; ?>
        </p>

        <h1 id="news-article-title" class="article-view__title"><?= e($item['titulo']) ?></h1>

        <?php if (!empty($item['resumen'])): ?>
            <p class="article-view__summary"><?= e($item['resumen']) ?></p>
        <?php endif; ?>

        <div class="article-view__body">
            <?= nl2br(e($item['cuerpo'])) ?>
        </div>

        <p class="article-view__footer">
            <?php if (!empty($item['autor_nombre'])): ?>
                Por <?= e($item['autor_nombre']) ?> ·
            <?php endif; ?>
            <?= e(format_date($item['publicado_en'] ?? $item['creado_en'])) ?>
        </p>
    </div>
</article>
