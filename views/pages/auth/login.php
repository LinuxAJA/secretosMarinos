<?php
/**
 * ============================================================================
 * views/pages/auth/login.php — Formulario de inicio de sesión
 * ============================================================================
 * Variables: $errors (array de mensajes por campo o 'general')
 * Old input: get_old('correo')
 * CSRF: csrf_field()
 * ============================================================================
 */
$errors = $errors ?? [];
?>
<section class="auth-section" aria-labelledby="login-title">
    <div class="container auth-section__grid">
        <div class="auth-intro">
            <p class="auth-intro__brand"><?= e(APP_NAME) ?></p>
            <h1 id="login-title" class="auth-intro__title">Inicia sesión</h1>
            <p class="auth-intro__text">
                Accede para participar en campañas, reportar hallazgos y seguir tu progreso.
            </p>
        </div>

        <form class="auth-form" method="post" action="<?= url('/login') ?>" novalidate data-auth-form>
            <?= csrf_field() ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="form-alert form-alert--error" role="alert">
                    <?= e($errors['general']) ?>
                </div>
            <?php endif; ?>

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
                    autocomplete="current-password"
                    required
                    minlength="8"
                    aria-invalid="<?= isset($errors['password']) ? 'true' : 'false' ?>"
                >
                <?php if (!empty($errors['password'])): ?>
                    <p class="form-error"><?= e($errors['password']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn--primary btn--block">Entrar</button>

            <p class="auth-form__footer">
                ¿Aún no tienes cuenta?
                <a href="<?= url('/registro') ?>">Regístrate</a>
            </p>
        </form>
    </div>
</section>
