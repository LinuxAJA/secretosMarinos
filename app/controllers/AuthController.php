<?php
/**
 * ============================================================================
 * AuthController.php — Login, registro y logout
 * ============================================================================
 * Flujo HTTP:
 *   GET  /login     → muestra formulario
 *   POST /login     → valida CSRF + AuthService::attemptLogin
 *   GET  /registro  → muestra formulario
 *   POST /registro  → valida CSRF + AuthService::register
 *   POST /logout    → cierra sesión (mejor POST que GET: evita CSRF por link)
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    /** GET /login */
    public function showLogin(): void
    {
        AuthMiddleware::requireGuest();
        clear_old();

        $this->render('pages/auth/login', [
            'pageTitle' => 'Iniciar sesión',
            'errors'    => [],
        ]);
    }

    /** POST /login */
    public function login(): void
    {
        AuthMiddleware::requireGuest();
        require_csrf('/login');

        $correo   = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = $this->auth->attemptLogin($correo, $password);

        if (!$result['ok']) {
            flash_old(['correo' => $correo]);
            $this->render('pages/auth/login', [
                'pageTitle' => 'Iniciar sesión',
                'errors'    => $result['errors'] ?? [],
            ]);
            clear_old();
            return;
        }

        flash('success', '¡Bienvenido/a de nuevo, ' . ($result['user']['nombre'] ?? '') . '!');
        $this->redirect('/panel');
    }

    /** GET /registro */
    public function showRegister(): void
    {
        AuthMiddleware::requireGuest();
        clear_old();

        $this->render('pages/auth/register', [
            'pageTitle' => 'Crear cuenta',
            'errors'    => [],
        ]);
    }

    /** POST /registro */
    public function register(): void
    {
        AuthMiddleware::requireGuest();
        require_csrf('/registro');

        $input = [
            'nombre'           => trim($_POST['nombre'] ?? ''),
            'correo'           => trim($_POST['correo'] ?? ''),
            'password'         => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
        ];

        $result = $this->auth->register($input);

        if (!$result['ok']) {
            flash_old([
                'nombre' => $input['nombre'],
                'correo' => $input['correo'],
            ]);
            $this->render('pages/auth/register', [
                'pageTitle' => 'Crear cuenta',
                'errors'    => $result['errors'] ?? [],
            ]);
            clear_old();
            return;
        }

        flash('success', 'Cuenta creada correctamente. ¡Ya formas parte de Misterios Del Mar!');
        $this->redirect('/panel');
    }

    /** POST /logout */
    public function logout(): void
    {
        // Aunque no esté logueado, CSRF evita logout forzado por terceros
        require_csrf('/');
        $this->auth->logout();

        // Tras session_destroy hay que reiniciar sesión para poder usar flash
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name(SESSION_NAME);
            session_start();
        }

        flash('info', 'Sesión cerrada correctamente.');
        $this->redirect('/login');
    }
}
