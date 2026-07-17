<?php
/**
 * ============================================================================
 * views/pages/panel/index.php — Panel del usuario autenticado
 * ============================================================================
 */
$user = $user ?? current_user();
$rolLabel = [
    'admin'      => 'Administrador',
    'docente'    => 'Docente',
    'estudiante' => 'Estudiante',
][$user['rol'] ?? ''] ?? ($user['rol'] ?? 'Usuario');
?>
<section class="section panel-section" aria-labelledby="panel-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="panel-title" class="section__title">Hola, <?= e($user['nombre'] ?? '') ?></h1>
        <p class="section__lead">
            Este es tu espacio personal. Desde aquí seguirás tu progreso y accederás a las acciones de la plataforma.
        </p>

        <div class="panel-grid">
            <article class="panel-stat">
                <h2 class="panel-stat__label">Rol</h2>
                <p class="panel-stat__value"><?= e($rolLabel) ?></p>
            </article>
            <article class="panel-stat">
                <h2 class="panel-stat__label">Puntos ecológicos</h2>
                <p class="panel-stat__value"><?= e((string) ($user['puntos'] ?? 0)) ?></p>
            </article>
            <article class="panel-stat">
                <h2 class="panel-stat__label">Correo</h2>
                <p class="panel-stat__value panel-stat__value--sm"><?= e($user['correo'] ?? '') ?></p>
            </article>
        </div>

        <div class="panel-actions">
            <a class="btn btn--primary" href="<?= url('/educacion') ?>">Ir a biblioteca</a>
            <a class="btn btn--secondary" href="<?= url('/reportes') ?>">Ver reportes</a>
            <?php if (has_any_role(ROLE_ADMIN, ROLE_DOCENTE)): ?>
                <a class="btn btn--secondary" href="<?= url('/admin') ?>">Administración</a>
            <?php endif; ?>
        </div>
    </div>
</section>
