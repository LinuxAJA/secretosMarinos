<?php
/**
 * ============================================================================
 * views/partials/footer.php — Pie de página institucional
 * ============================================================================
 */
?>
<footer class="site-footer">
    <div class="container site-footer__inner">
        <p class="site-footer__brand"><?= e(APP_NAME) ?></p>
        <p class="site-footer__meta">
            Alfabetización oceánica · Acción ambiental · Formaci&oacute;n SENA
        </p>
        <p class="site-footer__copy">
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> · v<?= e(APP_VERSION) ?>
        </p>
    </div>
</footer>
