<?php /** Listado admin categorías — mutaciones solo admin */ ?>
<div class="admin-toolbar">
    <?php if (can_manage_categories()): ?>
        <a class="btn btn--primary" href="<?= url('/admin/categorias/crear') ?>">Nueva categoría</a>
    <?php else: ?>
        <p class="form-hint" style="margin:0">
            Vista de solo lectura. La taxonomía la gestiona el administrador.
        </p>
    <?php endif; ?>
</div>

<?php if (!$items): ?>
    <p class="empty-state">No hay categorías todavía.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Contenidos</th>
                    <?php if (can_manage_categories()): ?>
                        <th></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><strong><?= e($item['nombre']) ?></strong></td>
                        <td class="muted"><?= e($item['slug']) ?></td>
                        <td><?= (int) ($item['total_contenidos'] ?? 0) ?></td>
                        <?php if (can_manage_categories()): ?>
                            <td class="data-table__actions">
                                <a href="<?= url('/admin/categorias/' . $item['id'] . '/editar') ?>">Editar</a>
                                <form method="post" action="<?= url('/admin/categorias/' . $item['id'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar esta categoría?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="link-danger">Eliminar</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
