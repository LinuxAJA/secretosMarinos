<?php
/**
 * ============================================================================
 * views/partials/page-band.php — Cabecera fotográfica de listados
 * ============================================================================
 * Variables esperadas:
 *   $bandTitle  (string) título H1
 *   $bandLead   (string) subtítulo
 *   $bandImage  (string) URL de imagen (asset/upload)
 *   $bandKicker (string) opcional, default APP_NAME
 * ============================================================================
 */
$bandKicker = $bandKicker ?? APP_NAME;
$bandTitle = $bandTitle ?? '';
$bandLead = $bandLead ?? '';
$bandImage = $bandImage ?? asset('img/placeholder-marine.jpg');
?>
<header
    class="page-band"
    style="--band-image: url('<?= e($bandImage) ?>')"
    aria-labelledby="page-band-title"
>
    <div class="page-band__veil">
        <div class="container page-band__content">
            <p class="kicker"><?= e($bandKicker) ?></p>
            <h1 id="page-band-title" class="page-band__title"><?= e($bandTitle) ?></h1>
            <?php if ($bandLead !== ''): ?>
                <p class="page-band__lead"><?= e($bandLead) ?></p>
            <?php endif; ?>
        </div>
    </div>
</header>
