<?php
/** Listado admin de contenidos — acciones según autoría / rol */
$nivelLabel = ['basico' => 'Básico', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado'];
?>
<div class="admin-toolbar">
    <?php if (can_manage_content(null)): ?>
        <a class="btn btn--primary" href="<?= url('/admin/contenidos/crear') ?>">Nuevo contenido</a>
    <?php endif; ?>
    <?php if (is_docente() && !is_admin()): ?>
        <p class="form-hint" style="margin:0">Puedes editar o eliminar solo los contenidos que tú creaste.</p>
    <?php endif; ?>
</div>

<?php if (!$items): ?>
    <p class="empty-state">Aún no hay contenidos. Crea el primero.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Categoría</th>
                    <th>Nivel</th>
                    <th>Estado</th>
                    <th>Visitas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $canManage = can_manage_content($item); ?>
                    <tr>
                        <td>
                            <strong><?= e($item['titulo']) ?></strong>
                            <div class="muted"><?= e($item['slug']) ?></div>
                        </td>
                        <td><?= e($item['autor_nombre'] ?? '—') ?></td>
                        <td><?= e($item['categoria_nombre'] ?? '—') ?></td>
                        <td><?= e($nivelLabel[$item['nivel']] ?? $item['nivel']) ?></td>
                        <td>
                            <span class="badge <?= (int)$item['publicado'] ? 'badge--ok' : 'badge--muted' ?>">
                                <?= (int)$item['publicado'] ? 'Publicado' : 'Borrador' ?>
                            </span>
                        </td>
                        <td><?= (int) $item['visitas'] ?></td>
                        <td class="data-table__actions">
                            <?php if ($canManage): ?>
                                <a href="<?= url('/admin/contenidos/' . $item['id'] . '/editar') ?>">Editar</a>
                                <form method="post" action="<?= url('/admin/contenidos/' . $item['id'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar este contenido?');">
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
