<?php
/**
 * Sidebar de administración (admin / docente).
 */
$user = current_user();
?>
<aside class="admin-sidebar" aria-label="Menú de administración">
    <a class="admin-sidebar__brand" href="<?= url('/admin') ?>"><?= e(APP_NAME) ?></a>
    <p class="admin-sidebar__user"><?= e($user['nombre'] ?? '') ?> · <?= e($user['rol'] ?? '') ?></p>

    <nav class="admin-sidebar__nav">
        <a href="<?= url('/admin') ?>">Dashboard</a>
        <a href="<?= url('/admin/contenidos') ?>">Contenidos</a>
        <a href="<?= url('/admin/categorias') ?>">Categorías</a>
        <a href="<?= url('/admin/noticias') ?>">Noticias</a>
        <a href="<?= url('/admin/ecosistemas') ?>">Ecosistemas</a>
        <a href="<?= url('/admin/especies') ?>">Especies</a>
        <a href="<?= url('/admin/campanias') ?>">Campañas</a>
        <a href="<?= url('/admin/reportes') ?>">Reportes</a>
        <a href="<?= url('/admin/insignias') ?>">Insignias</a>
        <?php if (can_adjust_points()): ?>
            <a href="<?= url('/admin/puntos') ?>">Puntos</a>
        <?php endif; ?>
        <a href="<?= url('/panel') ?>">Mi panel</a>
    </nav>

    <form method="post" action="<?= url('/logout') ?>" class="admin-sidebar__logout">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn--secondary btn--block">Cerrar sesión</button>
    </form>
</aside>
