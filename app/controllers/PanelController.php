<?php
/**
 * ============================================================================
 * PanelController.php — Panel personal + CRUD de perfil propio
 * ============================================================================
 * GET  /panel              → resumen + formularios
 * POST /panel/perfil       → actualiza nombre/correo
 * POST /panel/password     → cambia contraseña
 * POST /panel/eliminar     → elimina la propia cuenta
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\GamificationService;
use App\Services\ProfileService;

class PanelController extends Controller
{
    private UserRepository $users;
    private ProfileService $profiles;
    private AuthService $auth;
    private GamificationService $gamification;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->profiles = new ProfileService($this->users);
        $this->auth = new AuthService($this->users);
        $this->gamification = new GamificationService();
    }

    /** GET /panel */
    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $this->refreshSessionUser();
        $this->renderPanel();
    }

    /** POST /panel/perfil */
    public function updateProfile(): void
    {
        AuthMiddleware::requireAuth();
        require_csrf('/panel');

        $userId = (int) current_user()['id'];
        $input = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
        ];

        $result = $this->profiles->updateProfile($userId, $input);

        if (!$result['ok']) {
            flash_old($input);
            $this->refreshSessionUser();
            $this->renderPanel([
                'profileErrors' => $result['errors'] ?? [],
                'activeForm'    => 'profile',
            ]);
            clear_old();
            return;
        }

        $fresh = $result['user'];
        $_SESSION['user'] = [
            'id'     => (int) $fresh['id'],
            'nombre' => $fresh['nombre'],
            'correo' => $fresh['correo'],
            'rol'    => $fresh['rol'],
            'rol_id' => (int) $fresh['rol_id'],
            'puntos' => (int) $fresh['puntos'],
            'avatar' => $fresh['avatar'],
        ];

        flash('success', 'Perfil actualizado correctamente.');
        $this->redirect('/panel');
    }

    /** POST /panel/password */
    public function updatePassword(): void
    {
        AuthMiddleware::requireAuth();
        require_csrf('/panel');

        $userId = (int) current_user()['id'];
        $input = [
            'password_actual'  => $_POST['password_actual'] ?? '',
            'password'         => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
        ];

        $result = $this->profiles->changePassword($userId, $input);

        if (!$result['ok']) {
            $this->refreshSessionUser();
            $this->renderPanel([
                'passwordErrors' => $result['errors'] ?? [],
                'activeForm'     => 'password',
            ]);
            return;
        }

        session_regenerate_id(true);
        unset($_SESSION[CSRF_TOKEN_KEY]);
        csrf_token();

        flash('success', 'Contraseña actualizada correctamente.');
        $this->redirect('/panel');
    }

    /** POST /panel/eliminar */
    public function destroyAccount(): void
    {
        AuthMiddleware::requireAuth();
        require_csrf('/panel');

        $userId = (int) current_user()['id'];
        $input = [
            'password'          => $_POST['password'] ?? '',
            'confirmar_borrado' => !empty($_POST['confirmar_borrado']) ? '1' : '',
        ];

        $result = $this->profiles->deleteAccount($userId, $input);

        if (!$result['ok']) {
            $this->refreshSessionUser();
            $this->renderPanel([
                'deleteErrors' => $result['errors'] ?? [],
                'activeForm'   => 'delete',
            ]);
            return;
        }

        // Cierra sesión y reinicia para poder mostrar flash
        $this->auth->logout();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name(SESSION_NAME);
            session_start();
        }

        flash('info', 'Tu cuenta ha sido eliminada. Los contenidos que hayas publicado pueden permanecer sin autor.');
        $this->redirect('/');
    }

    /**
     * @param array<string,mixed> $extra
     */
    private function renderPanel(array $extra = []): void
    {
        $userId = (int) current_user()['id'];
        $this->render('pages/panel/index', array_merge([
            'pageTitle'      => 'Mi panel',
            'user'           => current_user(),
            'gamification'   => $this->gamification->panelSummary($userId),
            'profileErrors'  => [],
            'passwordErrors' => [],
            'deleteErrors'   => [],
            'activeForm'     => null,
        ], $extra));
    }

    /**
     * Recarga datos del usuario en sesión desde BD.
     */
    private function refreshSessionUser(): void
    {
        $fresh = $this->users->findById((int) current_user()['id']);

        if ($fresh && (int) $fresh['activo'] === 1) {
            $_SESSION['user'] = [
                'id'     => (int) $fresh['id'],
                'nombre' => $fresh['nombre'],
                'correo' => $fresh['correo'],
                'rol'    => $fresh['rol'],
                'rol_id' => (int) $fresh['rol_id'],
                'puntos' => (int) $fresh['puntos'],
                'avatar' => $fresh['avatar'],
            ];
            return;
        }

        flash('error', 'Tu cuenta no está disponible.');
        redirect_to('/login');
    }
}
