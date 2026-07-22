<?php
$errors ??= [];
$isEdit = !empty($item);
$value = static fn(string $key, mixed $default = ''): mixed =>
    old($key, $item[$key] ?? $default);
?>
<form class="admin-form" method="post" action="<?= e($action) ?>"
      enctype="multipart/form-data" novalidate>
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
            <label for="slug">Slug (opcional)</label>
            <input id="slug" name="slug" maxlength="140"
                   value="<?= e((string) $value('slug')) ?>"
                   placeholder="Se genera desde el nombre">
        </div>
    </div>

    <div class="form-field">
        <label for="descripcion">Descripción *</label>
        <textarea id="descripcion" name="descripcion" rows="5" required><?= e((string) $value('descripcion')) ?></textarea>
        <?php if (!empty($errors['descripcion'])): ?><p class="form-error"><?= e($errors['descripcion']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="funcion_ecologica">Función ecológica</label>
        <textarea id="funcion_ecologica" name="funcion_ecologica" rows="4"><?= e((string) $value('funcion_ecologica')) ?></textarea>
    </div>

    <div class="form-field">
        <label for="amenazas">Amenazas</label>
        <textarea id="amenazas" name="amenazas" rows="4"><?= e((string) $value('amenazas')) ?></textarea>
    </div>

    <div class="form-field">
        <label for="buenas_practicas">Buenas prácticas</label>
        <textarea id="buenas_practicas" name="buenas_practicas" rows="4"><?= e((string) $value('buenas_practicas')) ?></textarea>
    </div>

    <div class="form-field">
        <label for="imagen">Imagen (JPG, PNG, WEBP o GIF; máximo 5 MB)</label>
        <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif">
        <?php if (!empty($errors['imagen'])): ?><p class="form-error"><?= e($errors['imagen']) ?></p><?php endif; ?>
        <?php if ($isEdit && !empty($item['imagen'])): ?>
            <img class="admin-image-preview" src="<?= e(upload_url($item['imagen'])) ?>"
                 alt="Imagen actual de <?= e($item['nombre']) ?>">
            <label class="check-row">
                <input type="checkbox" name="eliminar_imagen" value="1">
                Eliminar imagen actual
            </label>
        <?php endif; ?>
    </div>

    <label class="check-row">
        <input type="checkbox" name="publicado" value="1"
               <?= $value('publicado', 0) ? 'checked' : '' ?>>
        Publicar en el catálogo
    </label>

    <div class="admin-form__actions">
        <button class="btn btn--primary" type="submit"><?= $isEdit ? 'Guardar cambios' : 'Crear ecosistema' ?></button>
        <a class="btn btn--secondary" href="<?= url('/admin/ecosistemas') ?>">Cancelar</a>
    </div>
</form>
