<?php
/** Formulario admin para ajustar puntos de un usuario. */
$errors ??= [];
$userId = (string) old('usuario_id', '');
?>
<p class="form-hint">
    Los ajustes quedan en el historial. Un saldo suficiente puede desbloquear insignias automáticamente.
    No se permiten saldos negativos.
</p>

<form class="admin-form" method="post" action="<?= url('/admin/puntos') ?>" novalidate>
    <?= csrf_field() ?>

    <div class="form-field">
        <label for="usuario_id">Usuario *</label>
        <select id="usuario_id" name="usuario_id" required>
            <option value="">Selecciona…</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= (int) $u['id'] ?>" <?= $userId === (string) $u['id'] ? 'selected' : '' ?>>
                    <?= e($u['nombre']) ?> (<?= e($u['rol']) ?>) — <?= (int) $u['puntos'] ?> pts
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['usuario_id'])): ?><p class="form-error"><?= e($errors['usuario_id']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="puntos">Puntos (+/−) *</label>
        <input type="number" id="puntos" name="puntos" required
               value="<?= e((string) old('puntos', '')) ?>"
               placeholder="Ej. 20 o -5">
        <?php if (!empty($errors['puntos'])): ?><p class="form-error"><?= e($errors['puntos']) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="motivo">Motivo *</label>
        <textarea id="motivo" name="motivo" rows="3" required maxlength="255"><?= e((string) old('motivo')) ?></textarea>
        <?php if (!empty($errors['motivo'])): ?><p class="form-error"><?= e($errors['motivo']) ?></p><?php endif; ?>
    </div>

    <div class="admin-form__actions">
        <button class="btn btn--primary" type="submit">Aplicar ajuste</button>
        <a class="btn btn--secondary" href="<?= url('/admin/insignias') ?>">Volver a insignias</a>
    </div>
</form>
