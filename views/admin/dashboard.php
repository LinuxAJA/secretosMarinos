<?php
/** Dashboard admin — Pasos 3 a 6 */
?>
<section class="admin-dashboard">
    <p class="section__lead">
        Gestiona educación, catálogo científico, participación ciudadana y gamificación.
    </p>

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
            <h2 class="panel-stat__label">Ecosistemas</h2>
            <p class="panel-stat__value"><?= (int) $stats['ecosistemas'] ?></p>
            <a href="<?= url('/admin/ecosistemas') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Especies</h2>
            <p class="panel-stat__value"><?= (int) $stats['especies'] ?></p>
            <a href="<?= url('/admin/especies') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Campañas</h2>
            <p class="panel-stat__value"><?= (int) $stats['campanias'] ?></p>
            <a href="<?= url('/admin/campanias') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Reportes</h2>
            <p class="panel-stat__value"><?= (int) $stats['reportes'] ?></p>
            <p class="muted"><?= (int) $stats['reportes_pendientes'] ?> pendientes</p>
            <a href="<?= url('/admin/reportes') ?>">Revisar cola</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Insignias</h2>
            <p class="panel-stat__value"><?= (int) $stats['insignias'] ?></p>
            <a href="<?= url('/admin/insignias') ?>">Administrar</a>
        </article>
        <article class="panel-stat">
            <h2 class="panel-stat__label">Accesos rápidos</h2>
            <p class="panel-actions" style="margin-top:0.75rem">
                <a class="btn btn--primary" href="<?= url('/admin/contenidos/crear') ?>">Nuevo contenido</a>
                <a class="btn btn--secondary" href="<?= url('/admin/reportes') ?>">Reportes</a>
                <?php if (can_manage_badges()): ?>
                    <a class="btn btn--secondary" href="<?= url('/admin/puntos') ?>">Ajustar puntos</a>
                <?php endif; ?>
                <?php if (can_manage_categories()): ?>
                    <a class="btn btn--secondary" href="<?= url('/admin/categorias/crear') ?>">Nueva categoría</a>
                <?php endif; ?>
            </p>
            <?php if (is_docente() && !is_admin()): ?>
                <p class="form-hint">
                    Como docente gestionas tus contenidos, noticias, especies y campañas.
                    Revisas reportes; categorías, ecosistemas e insignias son de solo lectura.
                </p>
            <?php endif; ?>
        </article>
    </div>
</section>
