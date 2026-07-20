<?php
$errors = $errors ?? [];
$isEdit = !empty($item);
$nombre = (string) old('nombre', $item['nombre'] ?? '');
$slug = (string) old('slug', $item['slug'] ?? '');
$descripcion = (string) old('descripcion', $item['descripcion'] ?? '');
?>
<form class="admin-form" method="post" action="<?= e($action) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="form-field">
        <label for="nombre">Nombre *</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" value="<?= e($nombre) ?>">
        <?php if (!empty($errors['nombre'])): ?><p class="form-error"><?= e($errors['nombre']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="slug">Slug (opcional)</label>
        <input type="text" id="slug" name="slug" maxlength="120" value="<?= e($slug) ?>">
    </div>

    <div class="form-field">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3"><?= e($descripcion) ?></textarea>
    </div>

    <div class="admin-form__actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Guardar' : 'Crear categoría' ?></button>
        <a class="btn btn--secondary" href="<?= url('/admin/categorias') ?>">Cancelar</a>
    </div>
</form>
