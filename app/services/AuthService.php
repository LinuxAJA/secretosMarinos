<?php
/**
 * ============================================================================
 * AuthService.php — Reglas de negocio de autenticación
 * ============================================================================
 * El Service concentra la lógica (validar, hashear, iniciar sesión).
 * El Controller solo recibe HTTP y delega aquí.
 *
 * Responsabilidades:
 *   - Validar datos de login / registro
 *   - Verificar password_hash
 *   - Crear / destruir sesión de forma segura
 *   - Limitar intentos fallidos de login (anti fuerza bruta básica)
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $users;

    public function __construct(?UserRepository $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    /**
     * Intenta autenticar con correo y contraseña.
     *
     * @return array{ok:bool, errors?:array<string,string>, user?:array}
     */
    public function attemptLogin(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $errors = [];

        // --- Bloqueo temporal por demasiados intentos ---
        if ($this->isLoginLocked()) {
            $errors['general'] = 'Demasiados intentos fallidos. Espera unos minutos e inténtalo de nuevo.';
            return ['ok' => false, 'errors' => $errors];
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'Ingresa un correo válido.';
        }
        if ($password === '') {
            $errors['password'] = 'Ingresa tu contraseña.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $user = $this->users->findByEmail($email);

        // Mensaje genérico: no revelamos si el correo existe o no (seguridad)
        $invalid = ['ok' => false, 'errors' => [
            'general' => 'Correo o contraseña incorrectos.',
        ]];

        if (!$user || !(int) $user['activo']) {
            $this->registerFailedAttempt();
            return $invalid;
        }

        // password_verify compara el texto plano con el hash bcrypt de la BD
        if (!password_verify($password, $user['password_hash'])) {
            $this->registerFailedAttempt();
            return $invalid;
        }

        // Login correcto: limpia contador y crea sesión
        $this->clearFailedAttempts();
        $this->loginUser($user);

        return ['ok' => true, 'user' => $this->publicUser($user)];
    }

    /**
     * Registra un nuevo usuario con rol estudiante por defecto.
     *
     * @return array{ok:bool, errors?:array<string,string>, user?:array}
     */
    public function register(array $input): array
    {
        $nombre   = trim($input['nombre'] ?? '');
        $email    = strtolower(trim($input['correo'] ?? ''));
        $password = $input['password'] ?? '';
        $confirm  = $input['password_confirm'] ?? '';
        $errors   = [];

        // --- Validaciones de negocio ---
        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $errors['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        } elseif (mb_strlen($nombre) > 100) {
            $errors['nombre'] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'Ingresa un correo válido.';
        } elseif ($this->users->emailExists($email)) {
            $errors['correo'] = 'Este correo ya está registrado.';
        }

        if (mb_strlen($password) < 8) {
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $confirm) {
            $errors['password_confirm'] = 'Las contraseñas no coinciden.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        // Rol por defecto al autoregistrarse: estudiante (ciudadanía)
        $rolId = $this->users->getRoleIdByName(ROLE_ESTUDIANTE);
        if ($rolId === null) {
            return ['ok' => false, 'errors' => [
                'general' => 'No se pudo asignar el rol. Contacta al administrador.',
            ]];
        }

        // Nunca guardar la contraseña en texto plano
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $id = $this->users->create([
            'rol_id'        => $rolId,
            'nombre'        => $nombre,
            'correo'        => $email,
            'password_hash' => $hash,
        ]);

        $user = $this->users->findById($id);
        if (!$user) {
            return ['ok' => false, 'errors' => [
                'general' => 'Cuenta creada, pero no se pudo iniciar sesión automáticamente.',
            ]];
        }

        $this->loginUser($user);

        return ['ok' => true, 'user' => $this->publicUser($user)];
    }

    /**
     * Guarda en sesión solo los datos necesarios (sin password_hash).
     * Regenera el ID de sesión para evitar session fixation.
     */
    public function loginUser(array $user): void
    {
        // Regenerar ID de sesión al autenticarse (medida obligatoria)
        session_regenerate_id(true);

        $_SESSION['user'] = $this->publicUser($user);

        // Nuevo token CSRF tras login
        unset($_SESSION[CSRF_TOKEN_KEY]);
        csrf_token();
    }

    /**
     * Cierra sesión de forma segura.
     */
    public function logout(): void
    {
        $_SESSION = [];

        // Borra la cookie de sesión en el navegador
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Datos seguros para guardar en sesión / devolver al controlador.
     */
    private function publicUser(array $user): array
    {
        return [
            'id'     => (int) $user['id'],
            'nombre' => $user['nombre'],
            'correo' => $user['correo'],
            'rol'    => $user['rol'],
            'rol_id' => (int) $user['rol_id'],
            'puntos' => (int) ($user['puntos'] ?? 0),
            'avatar' => $user['avatar'] ?? null,
        ];
    }

    // -----------------------------------------------------------------------
    // Limitación de intentos de login (en sesión; suficiente para V1.0 local)
    // -----------------------------------------------------------------------

    private function registerFailedAttempt(): void
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = ['count' => 0, 'locked_until' => 0];
        }
        $_SESSION['login_attempts']['count']++;

        if ($_SESSION['login_attempts']['count'] >= LOGIN_MAX_ATTEMPTS) {
            $_SESSION['login_attempts']['locked_until'] = time() + (LOGIN_LOCK_MINUTES * 60);
        }
    }

    private function clearFailedAttempts(): void
    {
        unset($_SESSION['login_attempts']);
    }

    private function isLoginLocked(): bool
    {
        $lockedUntil = $_SESSION['login_attempts']['locked_until'] ?? 0;
        if ($lockedUntil && time() < $lockedUntil) {
            return true;
        }
        // Si ya pasó el tiempo de bloqueo, reinicia contador
        if ($lockedUntil && time() >= $lockedUntil) {
            $this->clearFailedAttempts();
        }
        return false;
    }
}
