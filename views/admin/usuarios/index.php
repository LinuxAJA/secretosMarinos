<?php
/**
 * Listado admin de usuarios — Paso 7.
 * Solo administrador (la ruta ya está guardada en el controller).
 */
$filters ??= ['q' => '', 'rol' => '', 'activo' => ''];
$roles ??= [];
?>
<div class="admin-toolbar">
    <p class="form-hint" style="margin:0">
        Gestiona el rol y el estado de las cuentas. Nombre, correo y contraseña
        los edita cada usuario en su panel personal.
    </p>
</div>

<form class="filter-bar filter-bar--species" method="get" action="<?= url('/admin/usuarios') ?>">
    <div class="form-field">
        <label for="q">Buscar</label>
        <input type="search" id="q" name="q" value="<?= e($filters['q'] ?? '') ?>"
               placeholder="Nombre o correo">
    </div>
    <div class="form-field">
        <label for="rol">Rol</label>
        <select id="rol" name="rol">
            <option value="">Todos</option>
            <?php foreach ($roles as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= ($filters['rol'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field">
        <label for="activo">Estado</label>
        <select id="activo" name="activo">
            <option value="">Todos</option>
            <option value="1" <?= ($filters['activo'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
            <option value="0" <?= ($filters['activo'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
        </select>
    </div>
    <button type="submit" class="btn btn--primary">Filtrar</button>
</form>

<?php if (!$items): ?>
    <p class="empty-state">No hay usuarios con esos filtros.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Puntos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><strong><?= e($item['nombre']) ?></strong></td>
                        <td><?= e($item['correo']) ?></td>
                        <td><?= e($item['rol']) ?></td>
                        <td><?= (int) $item['puntos'] ?></td>
                        <td>
                            <span class="badge <?= (int) $item['activo'] ? 'badge--ok' : 'badge--muted' ?>">
                                <?= (int) $item['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td class="data-table__actions">
                            <a href="<?= url('/admin/usuarios/' . $item['id']) ?>">Ver</a>
                            <a href="<?= url('/admin/usuarios/' . $item['id'] . '/editar') ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
