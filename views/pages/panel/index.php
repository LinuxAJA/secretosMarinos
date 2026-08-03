<?php
/**
 * ============================================================================
 * views/pages/panel/index.php — Panel personal + edición de perfil
 * ============================================================================
 * Vars: $user, $profileErrors, $passwordErrors, $activeForm
 * ============================================================================
 */
$user = $user ?? current_user();
$profileErrors = $profileErrors ?? [];
$passwordErrors = $passwordErrors ?? [];
$deleteErrors = $deleteErrors ?? [];
$activeForm = $activeForm ?? null;
$gamification = $gamification ?? [
    'puntos' => (int) ($user['puntos'] ?? 0),
    'badges' => [],
    'next' => null,
    'history' => [],
    'progress' => 0,
];

$rolLabel = [
    'admin'      => 'Administrador',
    'docente'    => 'Docente',
    'estudiante' => 'Estudiante',
][$user['rol'] ?? ''] ?? ($user['rol'] ?? 'Usuario');

$nombreValue = (string) old('nombre', $user['nombre'] ?? '');
$correoValue = (string) old('correo', $user['correo'] ?? '');
?>
<section class="section panel-section" aria-labelledby="panel-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="panel-title" class="section__title">Hola, <?= e($user['nombre'] ?? '') ?></h1>
        <p class="section__lead">
            Gestiona tu cuenta, revisa tu rol y accede a las acciones de la plataforma.
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
            <a class="btn btn--secondary" href="<?= url('/reportes') ?>">Mis reportes</a>
            <a class="btn btn--secondary" href="<?= url('/insignias') ?>">Insignias</a>
            <a class="btn btn--secondary" href="<?= url('/ranking') ?>">Ranking</a>
            <?php if (has_any_role(ROLE_ADMIN, ROLE_DOCENTE)): ?>
                <a class="btn btn--secondary" href="<?= url('/admin') ?>">Administración</a>
            <?php endif; ?>
        </div>

        <section class="profile-card gamification-card" aria-labelledby="progress-title">
            <h2 id="progress-title" class="profile-card__title">Progreso ecológico</h2>
            <p class="profile-card__lead">
                Saldo actual: <strong><?= (int) $gamification['puntos'] ?> puntos</strong>.
                <?php if (!empty($gamification['next'])): ?>
                    Siguiente insignia: <?= e($gamification['next']['nombre']) ?>
                    (<?= (int) $gamification['next']['puntos_requeridos'] ?> pts).
                <?php else: ?>
                    ¡Has alcanzado todas las insignias activas disponibles!
                <?php endif; ?>
            </p>

            <div class="progress-bar" role="progressbar"
                 aria-valuenow="<?= (int) $gamification['progress'] ?>" aria-valuemin="0" aria-valuemax="100"
                 aria-label="Progreso hacia la siguiente insignia">
                <span class="progress-bar__fill" style="width: <?= (int) $gamification['progress'] ?>%"></span>
            </div>

            <?php if ($gamification['badges']): ?>
                <h3 class="gamification-subtitle">Mis insignias</h3>
                <ul class="badge-list">
                    <?php foreach ($gamification['badges'] as $badge): ?>
                        <li>
                            <span class="badge-icon badge-icon--sm badge-icon--<?= e($badge['icono'] ?: 'default') ?>" aria-hidden="true"></span>
                            <strong><?= e($badge['nombre']) ?></strong>
                            <span class="muted">· <?= e(format_date($badge['otorgada_en'] ?? null)) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="muted">Aún no tienes insignias. Crea reportes ambientales para sumar puntos.</p>
            <?php endif; ?>

            <?php if ($gamification['history']): ?>
                <h3 class="gamification-subtitle">Últimos movimientos</h3>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Puntos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gamification['history'] as $row): ?>
                                <tr>
                                    <td><?= e(format_date($row['creado_en'] ?? null)) ?></td>
                                    <td><?= e($row['motivo']) ?></td>
                                    <td><?= ((int) $row['puntos'] > 0 ? '+' : '') . (int) $row['puntos'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <div class="profile-grid">
            <!-- Datos de perfil -->
            <section class="profile-card" aria-labelledby="profile-form-title"
                     <?= $activeForm === 'profile' ? 'data-focus-form' : '' ?>>
                <h2 id="profile-form-title" class="profile-card__title">Mis datos</h2>
                <p class="profile-card__lead">Actualiza tu nombre y correo. El rol no se puede cambiar desde aquí.</p>

                <?php if (!empty($profileErrors['general'])): ?>
                    <div class="form-alert form-alert--error" role="alert"><?= e($profileErrors['general']) ?></div>
                <?php endif; ?>

                <form class="admin-form" method="post" action="<?= url('/panel/perfil') ?>" novalidate data-auth-form>
                    <?= csrf_field() ?>

                    <div class="form-field">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" required minlength="3" maxlength="100"
                               autocomplete="name" value="<?= e($nombreValue) ?>"
                               aria-invalid="<?= isset($profileErrors['nombre']) ? 'true' : 'false' ?>">
                        <?php if (!empty($profileErrors['nombre'])): ?>
                            <p class="form-error"><?= e($profileErrors['nombre']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" required
                               autocomplete="email" value="<?= e($correoValue) ?>"
                               aria-invalid="<?= isset($profileErrors['correo']) ? 'true' : 'false' ?>">
                        <?php if (!empty($profileErrors['correo'])): ?>
                            <p class="form-error"><?= e($profileErrors['correo']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label for="rol_readonly">Rol</label>
                        <input type="text" id="rol_readonly" value="<?= e($rolLabel) ?>" disabled
                               aria-describedby="rol-hint">
                        <p id="rol-hint" class="form-hint">Solo un administrador del sistema puede cambiar roles.</p>
                    </div>

                    <button type="submit" class="btn btn--primary">Guardar cambios</button>
                </form>
            </section>

            <!-- Cambio de contraseña -->
            <section class="profile-card" aria-labelledby="password-form-title"
                     <?= $activeForm === 'password' ? 'data-focus-form' : '' ?>>
                <h2 id="password-form-title" class="profile-card__title">Cambiar contraseña</h2>
                <p class="profile-card__lead">Por seguridad, confirma tu contraseña actual.</p>

                <form class="admin-form" method="post" action="<?= url('/panel/password') ?>" novalidate data-auth-form>
                    <?= csrf_field() ?>

                    <div class="form-field">
                        <label for="password_actual">Contraseña actual</label>
                        <input type="password" id="password_actual" name="password_actual" required
                               autocomplete="current-password"
                               aria-invalid="<?= isset($passwordErrors['password_actual']) ? 'true' : 'false' ?>">
                        <?php if (!empty($passwordErrors['password_actual'])): ?>
                            <p class="form-error"><?= e($passwordErrors['password_actual']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label for="password">Nueva contraseña</label>
                        <input type="password" id="password" name="password" required minlength="8"
                               autocomplete="new-password"
                               aria-invalid="<?= isset($passwordErrors['password']) ? 'true' : 'false' ?>">
                        <?php if (!empty($passwordErrors['password'])): ?>
                            <p class="form-error"><?= e($passwordErrors['password']) ?></p>
                        <?php else: ?>
                            <p class="form-hint">Mínimo 8 caracteres.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label for="password_confirm">Confirmar nueva contraseña</label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                               autocomplete="new-password"
                               aria-invalid="<?= isset($passwordErrors['password_confirm']) ? 'true' : 'false' ?>">
                        <?php if (!empty($passwordErrors['password_confirm'])): ?>
                            <p class="form-error"><?= e($passwordErrors['password_confirm']) ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn--primary">Actualizar contraseña</button>
                </form>
            </section>
        </div>

        <!-- Zona de peligro: eliminar cuenta -->
        <section class="profile-card profile-card--danger" aria-labelledby="delete-form-title"
                 <?= $activeForm === 'delete' ? 'data-focus-form' : '' ?>>
            <h2 id="delete-form-title" class="profile-card__title">Eliminar cuenta</h2>
            <p class="profile-card__lead">
                Esta acción es irreversible. Se borrará tu usuario; los contenidos o noticias que hayas publicado
                pueden permanecer en el sitio sin autor (integridad referencial <code>ON DELETE SET NULL</code>).
            </p>

            <?php if (!empty($deleteErrors['general'])): ?>
                <div class="form-alert form-alert--error" role="alert"><?= e($deleteErrors['general']) ?></div>
            <?php endif; ?>

            <form class="admin-form" method="post" action="<?= url('/panel/eliminar') ?>" novalidate
                  onsubmit="return confirm('¿Seguro que deseas eliminar tu cuenta de forma permanente?');">
                <?= csrf_field() ?>

                <div class="form-field">
                    <label for="delete_password">Contraseña actual</label>
                    <input type="password" id="delete_password" name="password" required
                           autocomplete="current-password"
                           aria-invalid="<?= isset($deleteErrors['password']) ? 'true' : 'false' ?>">
                    <?php if (!empty($deleteErrors['password'])): ?>
                        <p class="form-error"><?= e($deleteErrors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-field form-field--check">
                    <label>
                        <input type="checkbox" name="confirmar_borrado" value="1"
                               aria-invalid="<?= isset($deleteErrors['confirmar_borrado']) ? 'true' : 'false' ?>">
                        Entiendo que eliminar mi cuenta es permanente e irreversible.
                    </label>
                    <?php if (!empty($deleteErrors['confirmar_borrado'])): ?>
                        <p class="form-error"><?= e($deleteErrors['confirmar_borrado']) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn--danger">Eliminar mi cuenta</button>
            </form>
        </section>
    </div>
</section>
