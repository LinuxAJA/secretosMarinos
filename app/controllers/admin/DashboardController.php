<?php
/**
 * ============================================================================
 * Admin/DashboardController.php — Panel de administración (Paso 3)
 * ============================================================================
 * Acceso: admin y docente.
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\ContentRepository;
use App\Repositories\NewsRepository;

class DashboardController extends Controller
{
    /** GET /admin */
    public function index(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);

        $contents = new ContentRepository();
        $news = new NewsRepository();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Administración',
            'stats'     => [
                'contenidos' => $contents->countAll(),
                'noticias'   => $news->countAll(),
            ],
        ], 'admin');
    }
}
