<?php /** Administración de insignias. */ ?>
<div class="admin-toolbar">
    <?php if (can_manage_badges()): ?>
        <a class="btn btn--primary" href="<?= url('/admin/insignias/crear') ?>">Nueva insignia</a>
        <a class="btn btn--secondary" href="<?= url('/admin/puntos') ?>">Ajustar puntos</a>
    <?php else: ?>
        <p class="form-hint">Vista de solo lectura. Las insignias las gestiona el administrador.</p>
    <?php endif; ?>
</div>

<?php if (!$items): ?>
    <p class="empty-state">No hay insignias registradas.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Insignia</th>
                    <th>Código</th>
                    <th>Umbral</th>
                    <th>Estado</th>
                    <?php if (can_manage_badges()): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?= e($item['nombre']) ?></strong>
                            <div class="muted"><?= e($item['descripcion']) ?></div>
                        </td>
                        <td><code><?= e($item['codigo']) ?></code></td>
                        <td><?= (int) $item['puntos_requeridos'] ?> pts</td>
                        <td>
                            <span class="badge <?= (int) $item['activa'] ? 'badge--ok' : 'badge--muted' ?>">
                                <?= (int) $item['activa'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </td>
                        <?php if (can_manage_badges()): ?>
                            <td class="data-table__actions">
                                <a href="<?= url('/admin/insignias/' . $item['id'] . '/editar') ?>">Editar</a>
                                <form method="post" action="<?= url('/admin/insignias/' . $item['id'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar esta insignia? Se quitará de los usuarios que la tengan.');">
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
