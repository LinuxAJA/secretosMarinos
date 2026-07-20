<?php
$errors = $errors ?? [];
$isEdit = !empty($item);

$titulo = (string) old('titulo', $item['titulo'] ?? '');
$slug = (string) old('slug', $item['slug'] ?? '');
$resumen = (string) old('resumen', $item['resumen'] ?? '');
$cuerpo = (string) old('cuerpo', $item['cuerpo'] ?? '');
$categoria = (string) old('categoria', $item['categoria'] ?? '');
$publicada = old('publicada', !empty($item['publicada']) ? '1' : '');
$destacada = old('destacada', !empty($item['destacada']) ? '1' : '');
?>
<form class="admin-form" method="post" action="<?= e($action) ?>" novalidate>
    <?= csrf_field() ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="form-alert form-alert--error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="form-field">
        <label for="titulo">Título *</label>
        <input type="text" id="titulo" name="titulo" required maxlength="200" value="<?= e($titulo) ?>">
        <?php if (!empty($errors['titulo'])): ?><p class="form-error"><?= e($errors['titulo']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="slug">Slug (opcional)</label>
        <input type="text" id="slug" name="slug" maxlength="220" value="<?= e($slug) ?>">
    </div>

    <div class="form-field">
        <label for="categoria">Categoría (texto libre)</label>
        <input type="text" id="categoria" name="categoria" maxlength="80" value="<?= e($categoria) ?>"
               placeholder="Ej. institucional, campañas, ciencia">
    </div>

    <div class="form-field">
        <label for="resumen">Resumen</label>
        <textarea id="resumen" name="resumen" rows="2" maxlength="500"><?= e($resumen) ?></textarea>
    </div>

    <div class="form-field">
        <label for="cuerpo">Cuerpo *</label>
        <textarea id="cuerpo" name="cuerpo" rows="12" required><?= e($cuerpo) ?></textarea>
        <?php if (!empty($errors['cuerpo'])): ?><p class="form-error"><?= e($errors['cuerpo']) ?></p><?php endif; ?>
    </div>

    <div class="form-field form-field--check">
        <label>
            <input type="checkbox" name="publicada" value="1" <?= $publicada ? 'checked' : '' ?>>
            Publicar
        </label>
    </div>

    <div class="form-field form-field--check">
        <label>
            <input type="checkbox" name="destacada" value="1" <?= $destacada ? 'checked' : '' ?>>
            Marcar como destacada
        </label>
    </div>

    <div class="admin-form__actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Guardar cambios' : 'Crear noticia' ?></button>
        <a class="btn btn--secondary" href="<?= url('/admin/noticias') ?>">Cancelar</a>
    </div>
</form>
