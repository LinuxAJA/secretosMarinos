<?php
/**
 * Formulario crear/editar contenido.
 * Vars: $item, $categories, $errors, $action
 */
$errors = $errors ?? [];
$isEdit = !empty($item);

$titulo = (string) old('titulo', $item['titulo'] ?? '');
$slug = (string) old('slug', $item['slug'] ?? '');
$resumen = (string) old('resumen', $item['resumen'] ?? '');
$cuerpo = (string) old('cuerpo', $item['cuerpo'] ?? '');
$nivel = (string) old('nivel', $item['nivel'] ?? 'basico');
$categoriaId = (string) old('categoria_id', isset($item['categoria_id']) ? (string) $item['categoria_id'] : '');
$publicado = old('publicado', !empty($item['publicado']) ? '1' : '');
?>
<form class="admin-form" method="post" action="<?= e($action) ?>" novalidate>
    <?= csrf_field() ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="form-alert form-alert--error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="form-field">
        <label for="titulo">Título *</label>
        <input type="text" id="titulo" name="titulo" required maxlength="200" value="<?= e($titulo) ?>"
               aria-invalid="<?= isset($errors['titulo']) ? 'true' : 'false' ?>">
        <?php if (!empty($errors['titulo'])): ?><p class="form-error"><?= e($errors['titulo']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="slug">Slug (opcional)</label>
        <input type="text" id="slug" name="slug" maxlength="220" value="<?= e($slug) ?>"
               placeholder="Se genera automáticamente desde el título">
        <p class="form-hint">URL amigable. Si lo dejas vacío, se crea solo.</p>
    </div>

    <div class="form-grid-2">
        <div class="form-field">
            <label for="categoria_id">Categoría</label>
            <select id="categoria_id" name="categoria_id">
                <option value="">Sin categoría</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= $categoriaId === (string) $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['categoria_id'])): ?><p class="form-error"><?= e($errors['categoria_id']) ?></p><?php endif; ?>
        </div>

        <div class="form-field">
            <label for="nivel">Nivel</label>
            <select id="nivel" name="nivel">
                <?php foreach (['basico' => 'Básico', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $nivel === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-field">
        <label for="resumen">Resumen</label>
        <textarea id="resumen" name="resumen" rows="2" maxlength="500"><?= e($resumen) ?></textarea>
        <?php if (!empty($errors['resumen'])): ?><p class="form-error"><?= e($errors['resumen']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="cuerpo">Contenido *</label>
        <textarea id="cuerpo" name="cuerpo" rows="12" required><?= e($cuerpo) ?></textarea>
        <?php if (!empty($errors['cuerpo'])): ?><p class="form-error"><?= e($errors['cuerpo']) ?></p><?php endif; ?>
    </div>

    <div class="form-field form-field--check">
        <label>
            <input type="checkbox" name="publicado" value="1" <?= $publicado ? 'checked' : '' ?>>
            Publicar en la biblioteca
        </label>
    </div>

    <div class="admin-form__actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Guardar cambios' : 'Crear contenido' ?></button>
        <a class="btn btn--secondary" href="<?= url('/admin/contenidos') ?>">Cancelar</a>
    </div>
</form>
