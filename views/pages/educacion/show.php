<?php
/**
 * Lectura de un contenido educativo.
 * Var: $item
 */
$nivelLabel = ['basico' => 'Básico', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado'];
?>
<article class="section article-view" aria-labelledby="article-title">
    <div class="container article-view__inner">
        <p class="article-view__meta">
            <a href="<?= url('/educacion') ?>">Biblioteca</a>
            <?php if (!empty($item['categoria_nombre'])): ?>
                · <?= e($item['categoria_nombre']) ?>
            <?php endif; ?>
            · <?= e($nivelLabel[$item['nivel']] ?? $item['nivel']) ?>
            · <?= (int) $item['visitas'] ?> visitas
        </p>

        <h1 id="article-title" class="article-view__title"><?= e($item['titulo']) ?></h1>

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
            <?= e(format_date($item['creado_en'])) ?>
        </p>
    </div>
</article>
