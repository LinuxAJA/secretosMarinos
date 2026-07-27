<?php /** Cola administrativa de reportes ambientales. */ ?>
<div class="admin-toolbar">
    <p class="form-hint">
        Pendientes por revisar: <strong><?= (int) $pendingCount ?></strong>.
        Cambia el estado y deja una nota de seguimiento.
    </p>
</div>

<form class="filter-bar filter-bar--species" method="get" action="<?= url('/admin/reportes') ?>">
    <div class="form-field">
        <label for="estado">Estado</label>
        <select id="estado" name="estado">
            <option value="">Todos</option>
            <?php foreach ($states as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= ($filters['estado'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo">
            <option value="">Todos</option>
            <?php foreach ($types as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= ($filters['tipo'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn--primary">Filtrar</button>
</form>

<?php if (!$items): ?>
    <p class="empty-state">No hay reportes con esos filtros.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reporte</th>
                    <th>Autor</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?= e($item['titulo']) ?></strong>
                            <?php if (!empty($item['ubicacion'])): ?>
                                <div class="muted"><?= e($item['ubicacion']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= e($item['autor_nombre'] ?? '—') ?></td>
                        <td><?= e($types[$item['tipo']] ?? $item['tipo']) ?></td>
                        <td>
                            <span class="badge <?= ($item['estado'] ?? '') === 'resuelto' ? 'badge--ok' : (($item['estado'] ?? '') === 'pendiente' ? 'badge--warn' : 'badge--muted') ?>">
                                <?= e($states[$item['estado']] ?? $item['estado']) ?>
                            </span>
                        </td>
                        <td><?= e(format_date($item['creado_en'])) ?></td>
                        <td class="data-table__actions">
                            <a href="<?= url('/admin/reportes/' . $item['id'] . '/editar') ?>">Revisar</a>
                            <?php if (can_delete_any_report()): ?>
                                <form method="post" action="<?= url('/admin/reportes/' . $item['id'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar este reporte de forma permanente?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="link-danger">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
