<?php
/**
 * Formulario admin: solo rol y activo — Paso 7.
 * Nombre y correo se muestran deshabilitados (no se envían / no se persisten).
 */
$errors ??= [];
$rolValue = (string) old('rol', $user['rol'] ?? '');
$activoValue = (string) old('activo', (string) (int) ($user['activo'] ?? 0));
?>
<div class="admin-toolbar">
    <a class="btn btn--secondary" href="<?= url('/admin/usuarios/' . $user['id']) ?>">← Volver al detalle</a>
</div>

<?php if (!empty($errors['general'])): ?>
    <p class="form-error" role="alert"><?= e($errors['general']) ?></p>
<?php endif; ?>

<p class="form-hint">
    Solo puedes cambiar el rol y si la cuenta está activa.
    No se puede dejar el sistema sin al menos un administrador activo.
</p>

<form class="admin-form" method="post" action="<?= e($formAction) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="form-field">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" value="<?= e($user['nombre']) ?>" disabled>
    </div>

    <div class="form-field">
        <label for="correo">Correo</label>
        <input type="email" id="correo" value="<?= e($user['correo']) ?>" disabled>
    </div>

    <div class="form-field">
        <label for="rol">Rol *</label>
        <select id="rol" name="rol" required>
            <?php foreach ($roles as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $rolValue === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['rol'])): ?><p class="form-error"><?= e($errors['rol']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="activo">Estado *</label>
        <select id="activo" name="activo" required>
            <option value="1" <?= $activoValue === '1' ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= $activoValue === '0' ? 'selected' : '' ?>>Inactivo</option>
        </select>
        <?php if (!empty($errors['activo'])): ?><p class="form-error"><?= e($errors['activo']) ?></p><?php endif; ?>
        <p class="form-hint">Una cuenta inactiva no puede iniciar sesión.</p>
    </div>

    <div class="admin-form__actions">
        <button class="btn btn--primary" type="submit">Guardar cambios</button>
        <a class="btn btn--secondary" href="<?= url('/admin/usuarios/' . $user['id']) ?>">Cancelar</a>
    </div>
</form>
