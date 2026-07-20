<?php /** Listado admin noticias — acciones según autoría / rol */ ?>
<div class="admin-toolbar">
    <?php if (can_manage_news(null)): ?>
        <a class="btn btn--primary" href="<?= url('/admin/noticias/crear') ?>">Nueva noticia</a>
    <?php endif; ?>
    <?php if (is_docente() && !is_admin()): ?>
        <p class="form-hint" style="margin:0">Puedes editar o eliminar solo las noticias que tú creaste.</p>
    <?php endif; ?>
</div>

<?php if (!$items): ?>
    <p class="empty-state">Aún no hay noticias.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $canManage = can_manage_news($item); ?>
                    <tr>
                        <td>
                            <strong><?= e($item['titulo']) ?></strong>
                            <?php if ((int) $item['destacada']): ?>
                                <span class="badge">Destacada</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($item['autor_nombre'] ?? '—') ?></td>
                        <td><?= e($item['categoria'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= (int)$item['publicada'] ? 'badge--ok' : 'badge--muted' ?>">
                                <?= (int)$item['publicada'] ? 'Publicada' : 'Borrador' ?>
                            </span>
                        </td>
                        <td><?= e(format_date($item['publicado_en'] ?? $item['creado_en'])) ?></td>
                        <td class="data-table__actions">
                            <?php if ($canManage): ?>
                                <a href="<?= url('/admin/noticias/' . $item['id'] . '/editar') ?>">Editar</a>
                                <form method="post" action="<?= url('/admin/noticias/' . $item['id'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar esta noticia?');">
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
