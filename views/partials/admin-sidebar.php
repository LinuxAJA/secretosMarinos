<?php
/**
 * ============================================================================
 * Sidebar de administración — layout sticky + nav agrupada
 * ============================================================================
 * - Cabecera y logout fijos en viewport
 * - Solo el listado hace scroll interno (logout siempre visible)
 * ============================================================================
 */
$user = current_user();
$reqPath = rtrim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''), '/') ?: '/';

$adminActive = static function (string $route) use ($reqPath): bool {
    $target = rtrim((string) (parse_url(url($route), PHP_URL_PATH) ?: ''), '/') ?: '/';
    if ($route === '/admin') {
        return $reqPath === $target;
    }
    return $reqPath === $target || str_starts_with($reqPath, $target . '/');
};

$rolLabel = [
    'admin'   => 'Administrador',
    'docente' => 'Docente',
][$user['rol'] ?? ''] ?? ($user['rol'] ?? 'Usuario');

$link = static function (string $route, string $label) use ($adminActive): void {
    $active = $adminActive($route) ? ' is-active' : '';
    echo '<a class="admin-sidebar__link' . $active . '" href="' . e(url($route)) . '">' . e($label) . '</a>';
};
?>
<aside class="admin-sidebar" aria-label="Menú de administración">
    <div class="admin-sidebar__head">
        <a class="admin-sidebar__brand" href="<?= url('/admin') ?>">
            <span class="admin-sidebar__mark" aria-hidden="true"></span>
            <span class="admin-sidebar__brand-text"><?= e(APP_NAME) ?></span>
        </a>
        <div class="admin-sidebar__identity">
            <p class="admin-sidebar__name"><?= e($user['nombre'] ?? '') ?></p>
            <p class="admin-sidebar__role"><?= e($rolLabel) ?></p>
        </div>
    </div>

    <nav class="admin-sidebar__nav" aria-label="Secciones admin">
        <p class="admin-sidebar__group">Resumen</p>
        <?php $link('/admin', 'Dashboard'); ?>
        <?php if (can_view_stats()): ?>
            <?php $link('/admin/estadisticas', 'Estadísticas'); ?>
        <?php endif; ?>

        <p class="admin-sidebar__group">Biblioteca</p>
        <?php $link('/admin/contenidos', 'Contenidos'); ?>
        <?php $link('/admin/categorias', 'Categorías'); ?>
        <?php $link('/admin/noticias', 'Noticias'); ?>

        <p class="admin-sidebar__group">Catálogo</p>
        <?php $link('/admin/ecosistemas', 'Ecosistemas'); ?>
        <?php $link('/admin/especies', 'Especies'); ?>

        <p class="admin-sidebar__group">Acción</p>
        <?php $link('/admin/campanias', 'Campañas'); ?>
        <?php $link('/admin/reportes', 'Reportes'); ?>
        <?php $link('/admin/insignias', 'Insignias'); ?>
        <?php if (can_adjust_points()): ?>
            <?php $link('/admin/puntos', 'Puntos'); ?>
        <?php endif; ?>

        <?php if (can_manage_users()): ?>
            <p class="admin-sidebar__group">Sistema</p>
            <?php $link('/admin/usuarios', 'Usuarios'); ?>
        <?php endif; ?>

        <p class="admin-sidebar__group">Cuenta</p>
        <?php $link('/panel', 'Mi panel'); ?>
    </nav>

    <form method="post" action="<?= url('/logout') ?>" class="admin-sidebar__logout">
        <?= csrf_field() ?>
        <button type="submit" class="admin-sidebar__logout-btn">Cerrar sesión</button>
    </form>
</aside>
