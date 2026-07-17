<?php
/**
 * ============================================================================
 * views/pages/auth/register.php — Formulario de registro
 * ============================================================================
 * El autoregistro crea usuarios con rol "estudiante".
 * Admin/docente se asignan desde el panel (pasos siguientes).
 * ============================================================================
 */
$errors = $errors ?? [];
?>
<section class="auth-section" aria-labelledby="register-title">
    <div class="container auth-section__grid">
        <div class="auth-intro">
            <p class="auth-intro__brand"><?= e(APP_NAME) ?></p>
            <h1 id="register-title" class="auth-intro__title">Crea tu cuenta</h1>
            <p class="auth-intro__text">
                Únete como estudiante o ciudadanía participante y comienza a aprender y actuar por el océano.
            </p>
        </div>

        <form class="auth-form" method="post" action="<?= url('/registro') ?>" novalidate data-auth-form>
            <?= csrf_field() ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="form-alert form-alert--error" role="alert">
                    <?= e($errors['general']) ?>
                </div>
            <?php endif; ?>

            <div class="form-field">
                <label for="nombre">Nombre completo</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    autocomplete="name"
                    required
                    minlength="3"
                    maxlength="100"
                    value="<?= e(get_old('nombre')) ?>"
                    aria-invalid="<?= isset($errors['nombre']) ? 'true' : 'false' ?>"
                >
                <?php if (!empty($errors['nombre'])): ?>
                    <p class="form-error"><?= e($errors['nombre']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="correo">Correo electrónico</label>
                <input
                    type="email"
                    id="correo"
                    name="correo"
                    autocomplete="email"
                    required
                    value="<?= e(get_old('correo')) ?>"
                    aria-invalid="<?= isset($errors['correo']) ? 'true' : 'false' ?>"
                >
                <?php if (!empty($errors['correo'])): ?>
                    <p class="form-error"><?= e($errors['correo']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="password">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    required
                    minlength="8"
                    aria-invalid="<?= isset($errors['password']) ? 'true' : 'false' ?>"
                >
                <?php if (!empty($errors['password'])): ?>
                    <p class="form-error"><?= e($errors['password']) ?></p>
                <?php else: ?>
                    <p class="form-hint">Mínimo 8 caracteres.</p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="password_confirm">Confirmar contraseña</label>
                <input
                    type="password"
                    id="password_confirm"
                    name="password_confirm"
                    autocomplete="new-password"
                    required
                    minlength="8"
                    aria-invalid="<?= isset($errors['password_confirm']) ? 'true' : 'false' ?>"
                >
                <?php if (!empty($errors['password_confirm'])): ?>
                    <p class="form-error"><?= e($errors['password_confirm']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn--primary btn--block">Crear cuenta</button>

            <p class="auth-form__footer">
                ¿Ya tienes cuenta?
                <a href="<?= url('/login') ?>">Inicia sesión</a>
            </p>
        </form>
    </div>
</section>
