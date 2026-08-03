<?php
$errors ??= [];
$isEdit = !empty($item);
$value = static fn(string $key, mixed $default = ''): mixed =>
    old($key, $item[$key] ?? $default);
?>
<form class="admin-form" method="post" action="<?= e($action) ?>" novalidate>
    <?= csrf_field() ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="form-alert form-alert--error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="form-grid-2">
        <div class="form-field">
            <label for="nombre">Nombre *</label>
            <input id="nombre" name="nombre" required maxlength="120"
                   value="<?= e((string) $value('nombre')) ?>">
            <?php if (!empty($errors['nombre'])): ?><p class="form-error"><?= e($errors['nombre']) ?></p><?php endif; ?>
        </div>
        <div class="form-field">
            <label for="codigo">Código (slug)</label>
            <input id="codigo" name="codigo" maxlength="60"
                   value="<?= e((string) $value('codigo')) ?>"
                   placeholder="Se genera desde el nombre">
            <?php if (!empty($errors['codigo'])): ?><p class="form-error"><?= e($errors['codigo']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="form-field">
        <label for="descripcion">Descripción *</label>
        <textarea id="descripcion" name="descripcion" rows="3" required maxlength="255"><?= e((string) $value('descripcion')) ?></textarea>
        <?php if (!empty($errors['descripcion'])): ?><p class="form-error"><?= e($errors['descripcion']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="puntos_requeridos">Puntos requeridos *</label>
        <input type="number" id="puntos_requeridos" name="puntos_requeridos" min="0" required
               value="<?= e((string) $value('puntos_requeridos', '0')) ?>">
        <p class="form-hint">
            Define el umbral para desbloquear la insignia. El listado del catálogo
            se ordena automáticamente por este valor (de menor a mayor).
        </p>
        <?php if (!empty($errors['puntos_requeridos'])): ?><p class="form-error"><?= e($errors['puntos_requeridos']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="icono">Clave de icono</label>
        <input id="icono" name="icono" maxlength="120"
               value="<?= e((string) $value('icono')) ?>"
               placeholder="explorador, manglar, arrecife…">
        <p class="form-hint">Se usa como clase CSS visual (sin imágenes externas).</p>
    </div>

    <label class="check-row">
        <input type="checkbox" name="activa" value="1"
               <?= $value('activa', 1) ? 'checked' : '' ?>>
        Insignia activa en el catálogo
    </label>

    <div class="admin-form__actions">
        <button class="btn btn--primary" type="submit"><?= $isEdit ? 'Guardar cambios' : 'Crear insignia' ?></button>
        <a class="btn btn--secondary" href="<?= url('/admin/insignias') ?>">Cancelar</a>
    </div>
</form>
