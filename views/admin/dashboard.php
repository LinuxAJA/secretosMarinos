<?php
/** Dashboard admin — Paso 3 */
?>
<section class="admin-dashboard">
    <p class="section__lead">Gestiona la biblioteca educativa y las noticias del sitio.</p>

    <div class="panel-grid">
        <article class="panel-stat">
            <h2 class="panel-stat__label">Contenidos</h2>
            <p class="panel-stat__value"><?= (int) $stats['contenidos'] ?></p>
            <a href="<?= url('/admin/contenidos') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Noticias</h2>
            <p class="panel-stat__value"><?= (int) $stats['noticias'] ?></p>
            <a href="<?= url('/admin/noticias') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Accesos rápidos</h2>
            <p class="panel-actions" style="margin-top:0.75rem">
                <a class="btn btn--primary" href="<?= url('/admin/contenidos/crear') ?>">Nuevo contenido</a>
                <a class="btn btn--secondary" href="<?= url('/admin/noticias/crear') ?>">Nueva noticia</a>
                <?php if (can_manage_categories()): ?>
                    <a class="btn btn--secondary" href="<?= url('/admin/categorias/crear') ?>">Nueva categoría</a>
                <?php endif; ?>
            </p>
            <?php if (is_docente() && !is_admin()): ?>
                <p class="form-hint">Como docente gestionas solo tus contenidos y noticias. Las categorías son de solo lectura.</p>
            <?php endif; ?>
        </article>
    </div>
</section>
