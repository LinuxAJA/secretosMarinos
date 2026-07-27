<?php
/**
 * Formulario ciudadano para crear/editar reporte (solo pendiente en edición).
 */
$errors ??= [];
$isEdit = !empty($item);
$value = static fn(string $key, mixed $default = ''): mixed =>
    old($key, $item[$key] ?? $default);
$tipo = (string) $value('tipo', 'otro');
?>
<section class="section" aria-labelledby="report-form-title">
    <div class="container">
        <p class="article-view__meta"><a href="<?= url('/reportes') ?>">Reportes</a></p>
        <h1 id="report-form-title" class="section__title"><?= e($pageTitle) ?></h1>
        <p class="section__lead">Describe el problema con claridad. Puedes adjuntar una evidencia fotográfica.</p>

        <form class="admin-form" method="post" action="<?= e($action) ?>"
              enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="form-alert form-alert--error"><?= e($errors['general']) ?></div>
            <?php endif; ?>

            <div class="form-field">
                <label for="titulo">Título *</label>
                <input id="titulo" name="titulo" required maxlength="180"
                       value="<?= e((string) $value('titulo')) ?>">
                <?php if (!empty($errors['titulo'])): ?><p class="form-error"><?= e($errors['titulo']) ?></p><?php endif; ?>
            </div>

            <div class="form-grid-2">
                <div class="form-field">
                    <label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo" required>
                        <?php foreach ($types as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $tipo === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['tipo'])): ?><p class="form-error"><?= e($errors['tipo']) ?></p><?php endif; ?>
                </div>
                <div class="form-field">
                    <label for="ubicacion">Ubicación</label>
                    <input id="ubicacion" name="ubicacion" maxlength="255"
                           value="<?= e((string) $value('ubicacion')) ?>"
                           placeholder="Ej. Playa sector norte">
                </div>
            </div>

            <div class="form-field">
                <label for="descripcion">Descripción *</label>
                <textarea id="descripcion" name="descripcion" rows="6" required><?= e((string) $value('descripcion')) ?></textarea>
                <?php if (!empty($errors['descripcion'])): ?><p class="form-error"><?= e($errors['descripcion']) ?></p><?php endif; ?>
            </div>

            <div class="form-field">
                <label for="imagen">Evidencia (JPG, PNG, WEBP o GIF; máximo 5 MB)</label>
                <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif">
                <?php if (!empty($errors['imagen'])): ?><p class="form-error"><?= e($errors['imagen']) ?></p><?php endif; ?>
                <?php if ($isEdit && !empty($item['imagen'])): ?>
                    <img class="admin-image-preview" src="<?= e(upload_url($item['imagen'])) ?>"
                         alt="Evidencia actual">
                    <label class="check-row">
                        <input type="checkbox" name="eliminar_imagen" value="1">
                        Eliminar imagen actual
                    </label>
                <?php endif; ?>
            </div>

            <div class="admin-form__actions">
                <button class="btn btn--primary" type="submit">
                    <?= $isEdit ? 'Guardar cambios' : 'Enviar reporte' ?>
                </button>
                <a class="btn btn--secondary" href="<?= url('/reportes') ?>">Cancelar</a>
            </div>
        </form>
    </div>
</section>
