<?php
$errors ??= [];
$isEdit = !empty($item);
$value = static fn(string $key, mixed $default = ''): mixed =>
    old($key, $item[$key] ?? $default);
$ecosystemId = (string) $value('ecosistema_id');
$conservation = (string) $value('estado_conservacion');
?>
<form class="admin-form" method="post" action="<?= e($action) ?>"
      enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="form-alert form-alert--error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="form-grid-2">
        <div class="form-field">
            <label for="nombre_comun">Nombre común *</label>
            <input id="nombre_comun" name="nombre_comun" required maxlength="150"
                   value="<?= e((string) $value('nombre_comun')) ?>">
            <?php if (!empty($errors['nombre_comun'])): ?><p class="form-error"><?= e($errors['nombre_comun']) ?></p><?php endif; ?>
        </div>
        <div class="form-field">
            <label for="nombre_cientifico">Nombre científico *</label>
            <input id="nombre_cientifico" name="nombre_cientifico" required maxlength="150"
                   value="<?= e((string) $value('nombre_cientifico')) ?>">
            <?php if (!empty($errors['nombre_cientifico'])): ?><p class="form-error"><?= e($errors['nombre_cientifico']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="form-grid-2">
        <div class="form-field">
            <label for="slug">Slug (opcional)</label>
            <input id="slug" name="slug" maxlength="180"
                   value="<?= e((string) $value('slug')) ?>">
        </div>
        <div class="form-field">
            <label for="ecosistema_id">Ecosistema</label>
            <select id="ecosistema_id" name="ecosistema_id">
                <option value="">Sin ecosistema</option>
                <?php foreach ($ecosystems as $ecosystem): ?>
                    <option value="<?= (int) $ecosystem['id'] ?>"
                        <?= $ecosystemId === (string) $ecosystem['id'] ? 'selected' : '' ?>>
                        <?= e($ecosystem['nombre']) ?>
                        <?= isset($ecosystem['publicado']) && !(int) $ecosystem['publicado'] ? ' (borrador)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['ecosistema_id'])): ?><p class="form-error"><?= e($errors['ecosistema_id']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="form-grid-2">
        <div class="form-field">
            <label for="clasificacion">Clasificación</label>
            <input id="clasificacion" name="clasificacion" maxlength="255"
                   value="<?= e((string) $value('clasificacion')) ?>"
                   placeholder="Ej. Familia Scaridae">
        </div>
        <div class="form-field">
            <label for="estado_conservacion">Estado de conservación</label>
            <select id="estado_conservacion" name="estado_conservacion">
                <option value="">Sin definir</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?= e($state) ?>" <?= $conservation === $state ? 'selected' : '' ?>>
                        <?= e($state) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['estado_conservacion'])): ?><p class="form-error"><?= e($errors['estado_conservacion']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="form-field">
        <label for="descripcion">Descripción *</label>
        <textarea id="descripcion" name="descripcion" rows="6" required><?= e((string) $value('descripcion')) ?></textarea>
        <?php if (!empty($errors['descripcion'])): ?><p class="form-error"><?= e($errors['descripcion']) ?></p><?php endif; ?>
    </div>

    <?php foreach ([
        'habitat' => 'Hábitat',
        'distribucion' => 'Distribución',
        'amenazas' => 'Amenazas',
    ] as $key => $label): ?>
        <div class="form-field">
            <label for="<?= $key ?>"><?= $label ?></label>
            <textarea id="<?= $key ?>" name="<?= $key ?>" rows="3"><?= e((string) $value($key)) ?></textarea>
        </div>
    <?php endforeach; ?>

    <div class="form-field">
        <label for="imagen">Imagen (JPG, PNG, WEBP o GIF; máximo 5 MB)</label>
        <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif">
        <?php if (!empty($errors['imagen'])): ?><p class="form-error"><?= e($errors['imagen']) ?></p><?php endif; ?>
        <?php if ($isEdit && !empty($item['imagen'])): ?>
            <img class="admin-image-preview" src="<?= e(upload_url($item['imagen'])) ?>"
                 alt="Imagen actual de <?= e($item['nombre_comun']) ?>">
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
        <button class="btn btn--primary" type="submit"><?= $isEdit ? 'Guardar cambios' : 'Crear especie' ?></button>
        <a class="btn btn--secondary" href="<?= url('/admin/especies') ?>">Cancelar</a>
    </div>
</form>
