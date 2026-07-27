<?php
/**
 * Formulario admin de campañas.
 * El motivo de cancelación es obligatorio cuando estado = cancelada
 * (validado en CampaignService; UI lo resalta con JS).
 */
$errors ??= [];
$isEdit = !empty($item);
$value = static fn(string $key, mixed $default = ''): mixed =>
    old($key, $item[$key] ?? $default);
$estado = (string) $value('estado', 'borrador');
?>
<form class="admin-form" method="post" action="<?= e($action) ?>"
      enctype="multipart/form-data" novalidate data-campaign-form>
    <?= csrf_field() ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="form-alert form-alert--error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="form-grid-2">
        <div class="form-field">
            <label for="titulo">Título *</label>
            <input id="titulo" name="titulo" required maxlength="200"
                   value="<?= e((string) $value('titulo')) ?>">
            <?php if (!empty($errors['titulo'])): ?><p class="form-error"><?= e($errors['titulo']) ?></p><?php endif; ?>
        </div>
        <div class="form-field">
            <label for="slug">Slug (opcional)</label>
            <input id="slug" name="slug" maxlength="220"
                   value="<?= e((string) $value('slug')) ?>">
        </div>
    </div>

    <div class="form-field">
        <label for="descripcion">Descripción *</label>
        <textarea id="descripcion" name="descripcion" rows="5" required><?= e((string) $value('descripcion')) ?></textarea>
        <?php if (!empty($errors['descripcion'])): ?><p class="form-error"><?= e($errors['descripcion']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="objetivo">Objetivo</label>
        <textarea id="objetivo" name="objetivo" rows="3" maxlength="500"><?= e((string) $value('objetivo')) ?></textarea>
        <?php if (!empty($errors['objetivo'])): ?><p class="form-error"><?= e($errors['objetivo']) ?></p><?php endif; ?>
    </div>

    <div class="form-grid-2">
        <div class="form-field">
            <label for="fecha_inicio">Fecha de inicio</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio"
                   value="<?= e((string) $value('fecha_inicio')) ?>">
            <?php if (!empty($errors['fecha_inicio'])): ?><p class="form-error"><?= e($errors['fecha_inicio']) ?></p><?php endif; ?>
        </div>
        <div class="form-field">
            <label for="fecha_fin">Fecha de fin</label>
            <input type="date" id="fecha_fin" name="fecha_fin"
                   value="<?= e((string) $value('fecha_fin')) ?>">
            <?php if (!empty($errors['fecha_fin'])): ?><p class="form-error"><?= e($errors['fecha_fin']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="form-field">
        <label for="estado">Estado *</label>
        <select id="estado" name="estado" required data-campaign-estado>
            <?php foreach ($states as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $estado === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['estado'])): ?><p class="form-error"><?= e($errors['estado']) ?></p><?php endif; ?>
    </div>

    <div class="form-field cancel-reason-field" data-cancel-reason
         <?= $estado === 'cancelada' ? '' : 'hidden' ?>>
        <label for="motivo_cancelacion">Motivo de cancelación *</label>
        <textarea id="motivo_cancelacion" name="motivo_cancelacion" rows="4"
                  maxlength="500"
                  placeholder="Explica por qué se cancela la campaña (mínimo 15 caracteres)."><?= e((string) $value('motivo_cancelacion')) ?></textarea>
        <p class="form-hint">Obligatorio al cancelar. Queda visible en administración como justificación.</p>
        <?php if (!empty($errors['motivo_cancelacion'])): ?>
            <p class="form-error"><?= e($errors['motivo_cancelacion']) ?></p>
        <?php endif; ?>
        <?php if ($isEdit && !empty($item['cancelada_en'])): ?>
            <p class="muted">Cancelada el <?= e(format_date($item['cancelada_en'])) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($isEdit && ($item['estado'] ?? '') !== 'cancelada' && !empty($item['motivo_cancelacion'])): ?>
        <div class="form-alert form-alert--info">
            <strong>Historial de cancelación previa:</strong>
            <?= e($item['motivo_cancelacion']) ?>
        </div>
    <?php endif; ?>

    <div class="form-field">
        <label for="imagen">Imagen (JPG, PNG, WEBP o GIF; máximo 5 MB)</label>
        <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif">
        <?php if (!empty($errors['imagen'])): ?><p class="form-error"><?= e($errors['imagen']) ?></p><?php endif; ?>
        <?php if ($isEdit && !empty($item['imagen'])): ?>
            <img class="admin-image-preview" src="<?= e(upload_url($item['imagen'])) ?>"
                 alt="Imagen actual de <?= e($item['titulo']) ?>">
            <label class="check-row">
                <input type="checkbox" name="eliminar_imagen" value="1">
                Eliminar imagen actual
            </label>
        <?php endif; ?>
    </div>

    <div class="admin-form__actions">
        <button class="btn btn--primary" type="submit"><?= $isEdit ? 'Guardar cambios' : 'Crear campaña' ?></button>
        <a class="btn btn--secondary" href="<?= url('/admin/campanias') ?>">Cancelar</a>
    </div>
</form>
