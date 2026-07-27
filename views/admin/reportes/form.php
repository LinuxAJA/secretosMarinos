<?php
/**
 * Formulario de revisión de reportes (staff).
 * Muestra los datos del ciudadano en solo lectura y permite estado + notas.
 */
$errors ??= [];
$value = static fn(string $key, mixed $default = ''): mixed =>
    old($key, $item[$key] ?? $default);
$estado = (string) $value('estado');
?>
<div class="review-summary">
    <p><strong>Autor:</strong> <?= e($item['autor_nombre'] ?? '—') ?></p>
    <p><strong>Tipo:</strong> <?= e($types[$item['tipo']] ?? $item['tipo']) ?></p>
    <?php if (!empty($item['ubicacion'])): ?>
        <p><strong>Ubicación:</strong> <?= e($item['ubicacion']) ?></p>
    <?php endif; ?>
    <p><strong>Descripción:</strong></p>
    <p><?= nl2br(e($item['descripcion'])) ?></p>
    <?php if (!empty($item['imagen'])): ?>
        <img class="admin-image-preview" src="<?= e(upload_url($item['imagen'])) ?>"
             alt="Evidencia del reporte">
    <?php endif; ?>
    <p><a href="<?= url('/reportes/' . $item['id']) ?>" target="_blank" rel="noopener">Ver ficha pública/usuario</a></p>
</div>

<form class="admin-form" method="post" action="<?= e($action) ?>" novalidate>
    <?= csrf_field() ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="form-alert form-alert--error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="form-field">
        <label for="estado">Estado de revisión *</label>
        <select id="estado" name="estado" required>
            <?php foreach ($states as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $estado === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['estado'])): ?><p class="form-error"><?= e($errors['estado']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="notas_revision">Notas de revisión</label>
        <textarea id="notas_revision" name="notas_revision" rows="5"
                  maxlength="2000"
                  placeholder="Obligatorias al pasar a en revisión o resuelto."><?= e((string) $value('notas_revision')) ?></textarea>
        <?php if (!empty($errors['notas_revision'])): ?>
            <p class="form-error"><?= e($errors['notas_revision']) ?></p>
        <?php endif; ?>
    </div>

    <div class="admin-form__actions">
        <button class="btn btn--primary" type="submit">Guardar revisión</button>
        <a class="btn btn--secondary" href="<?= url('/admin/reportes') ?>">Volver</a>
    </div>
</form>
