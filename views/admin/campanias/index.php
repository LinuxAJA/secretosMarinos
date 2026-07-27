<?php /** Administración de campañas ambientales. */ ?>
<div class="admin-toolbar">
    <?php if (can_manage_campaigns()): ?>
        <a class="btn btn--primary" href="<?= url('/admin/campanias/crear') ?>">Nueva campaña</a>
    <?php endif; ?>
    <?php if (is_docente() && !is_admin()): ?>
        <p class="form-hint">Puedes editar o eliminar solo las campañas de tu responsabilidad.</p>
    <?php endif; ?>
</div>

<?php if (!$items): ?>
    <p class="empty-state">No hay campañas registradas.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Campaña</th>
                    <th>Responsable</th>
                    <th>Estado</th>
                    <th>Fechas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $canManage = can_manage_campaigns($item); ?>
                    <tr>
                        <td>
                            <strong><?= e($item['titulo']) ?></strong>
                            <div class="muted"><?= e($item['slug']) ?></div>
                            <?php if (($item['estado'] ?? '') === 'cancelada' && !empty($item['motivo_cancelacion'])): ?>
                                <p class="cancel-reason">
                                    <strong>Motivo de cancelación:</strong>
                                    <?= e(excerpt($item['motivo_cancelacion'], 120)) ?>
                                </p>
                            <?php endif; ?>
                        </td>
                        <td><?= e($item['responsable_nombre'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= ($item['estado'] ?? '') === 'activa' ? 'badge--ok' : (($item['estado'] ?? '') === 'cancelada' ? 'badge--danger' : 'badge--muted') ?>">
                                <?= e($states[$item['estado']] ?? $item['estado']) ?>
                            </span>
                        </td>
                        <td class="muted">
                            <?= e(format_date($item['fecha_inicio'] ?? null) ?: '—') ?>
                            <?php if (!empty($item['fecha_fin'])): ?>
                                <br>→ <?= e(format_date($item['fecha_fin'])) ?>
                            <?php endif; ?>
                        </td>
                        <td class="data-table__actions">
                            <?php if ($canManage): ?>
                                <a href="<?= url('/admin/campanias/' . $item['id'] . '/editar') ?>">Editar</a>
                                <form method="post" action="<?= url('/admin/campanias/' . $item['id'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Eliminar esta campaña?');">
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
