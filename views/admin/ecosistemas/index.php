<?php /** Administración de ecosistemas. */ ?>
<div class="admin-toolbar">
    <?php if (can_manage_ecosystems()): ?>
        <a class="btn btn--primary" href="<?= url('/admin/ecosistemas/crear') ?>">Nuevo ecosistema</a>
    <?php else: ?>
        <p class="form-hint">Vista de solo lectura. Los ecosistemas los gestiona el administrador.</p>
    <?php endif; ?>
</div>

<?php if (!$items): ?>
    <p class="empty-state">No hay ecosistemas registrados.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Especies</th>
                    <th>Actualizado</th>
                    <?php if (can_manage_ecosystems()): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?= e($item['nombre']) ?></strong>
                            <div class="muted"><?= e($item['slug']) ?></div>
                        </td>
                        <td>
                            <span class="badge <?= (int) $item['publicado'] ? 'badge--ok' : 'badge--muted' ?>">
                                <?= (int) $item['publicado'] ? 'Publicado' : 'Borrador' ?>
                            </span>
                        </td>
                        <td><?= (int) $item['total_especies'] ?></td>
                        <td><?= e(format_date($item['actualizado_en'] ?? $item['creado_en'])) ?></td>
                        <?php if (can_manage_ecosystems()): ?>
                            <td class="data-table__actions">
                                <a href="<?= url('/admin/ecosistemas/' . $item['id'] . '/editar') ?>">Editar</a>
                                <form method="post" action="<?= url('/admin/ecosistemas/' . $item['id'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar este ecosistema? Sus especies quedarán sin ecosistema asociado.');">
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
