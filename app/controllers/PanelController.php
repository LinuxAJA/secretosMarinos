<?php
/**
 * ============================================================================
 * PanelController.php — Área privada tras iniciar sesión
 * ============================================================================
 * Panel simple V1.0: muestra datos del usuario, rol y accesos rápidos.
 * Más adelante se ampliará con KPIs y gestión admin.
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\UserRepository;

class PanelController extends Controller
{
    /** GET /panel */
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        // Refrescamos datos desde BD por si cambiaron puntos/rol
        $repo = new UserRepository();
        $fresh = $repo->findById((int) current_user()['id']);

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
        }

        $this->render('pages/panel/index', [
            'pageTitle' => 'Mi panel',
            'user'      => current_user(),
        ]);
    }
}
