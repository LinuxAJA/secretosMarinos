<?php /** Administración de especies. */ ?>
<div class="admin-toolbar">
    <?php if (can_manage_species()): ?>
        <a class="btn btn--primary" href="<?= url('/admin/especies/crear') ?>">Nueva especie</a>
    <?php endif; ?>
    <?php if (is_docente() && !is_admin()): ?>
        <p class="form-hint">Puedes editar o eliminar solo las especies de tu autoría.</p>
    <?php endif; ?>
</div>

<?php if (!$items): ?>
    <p class="empty-state">No hay especies registradas.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Especie</th>
                    <th>Autor</th>
                    <th>Ecosistema</th>
                    <th>Conservación</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $canManage = can_manage_species($item); ?>
                    <tr>
                        <td>
                            <strong><?= e($item['nombre_comun']) ?></strong>
                            <div class="scientific-name"><?= e($item['nombre_cientifico']) ?></div>
                        </td>
                        <td><?= e($item['autor_nombre'] ?? '—') ?></td>
                        <td><?= e($item['ecosistema_nombre'] ?? '—') ?></td>
                        <td><?= e($item['estado_conservacion'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= (int) $item['publicado'] ? 'badge--ok' : 'badge--muted' ?>">
                                <?= (int) $item['publicado'] ? 'Publicada' : 'Borrador' ?>
                            </span>
                        </td>
                        <td class="data-table__actions">
                            <?php if ($canManage): ?>
                                <a href="<?= url('/admin/especies/' . $item['id'] . '/editar') ?>">Editar</a>
                                <form method="post" action="<?= url('/admin/especies/' . $item['id'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar esta especie?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="link-danger">Eliminar</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">Solo lectura</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
