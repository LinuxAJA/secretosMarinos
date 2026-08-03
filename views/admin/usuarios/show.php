<?php
/**
 * Detalle de usuario (solo lectura) — Paso 7.
 * La edición de rol/activo se hace en el formulario dedicado.
 */
$roleLabels = [
    ROLE_ADMIN => 'Administrador',
    ROLE_DOCENTE => 'Docente',
    ROLE_ESTUDIANTE => 'Estudiante',
];
$rolLabel = $roleLabels[$user['rol'] ?? ''] ?? (string) ($user['rol'] ?? '');
?>
<div class="admin-toolbar">
    <a class="btn btn--secondary" href="<?= url('/admin/usuarios') ?>">← Volver al listado</a>
    <a class="btn btn--primary" href="<?= url('/admin/usuarios/' . $user['id'] . '/editar') ?>">Editar rol / estado</a>
</div>

<section class="profile-card" style="max-width:40rem">
    <h2 class="profile-card__title"><?= e($user['nombre']) ?></h2>
    <p class="profile-card__lead">Datos de cuenta (solo lectura desde administración).</p>

    <dl class="stats-dl">
        <div>
            <dt>Correo</dt>
            <dd><?= e($user['correo']) ?></dd>
        </div>
        <div>
            <dt>Rol</dt>
            <dd><?= e($rolLabel) ?></dd>
        </div>
        <div>
            <dt>Estado</dt>
            <dd>
                <span class="badge <?= (int) $user['activo'] ? 'badge--ok' : 'badge--muted' ?>">
                    <?= (int) $user['activo'] ? 'Activo' : 'Inactivo' ?>
                </span>
            </dd>
        </div>
        <div>
            <dt>Puntos</dt>
            <dd><?= (int) $user['puntos'] ?></dd>
        </div>
        <div>
            <dt>Reportes creados</dt>
            <dd><?= (int) $reportCount ?></dd>
        </div>
        <div>
            <dt>Insignias obtenidas</dt>
            <dd><?= (int) $badgeCount ?></dd>
        </div>
        <?php if (!empty($user['creado_en'])): ?>
            <div>
                <dt>Alta</dt>
                <dd><?= e((string) $user['creado_en']) ?></dd>
            </div>
        <?php endif; ?>
    </dl>

    <p class="form-hint">
        Para cambiar nombre, correo o contraseña, el usuario debe usar
        <a href="<?= url('/panel') ?>">su panel personal</a>.
    </p>
</section>
