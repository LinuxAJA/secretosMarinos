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
use App\Repositories\EcosystemRepository;
use App\Repositories\NewsRepository;
use App\Repositories\SpeciesRepository;

class DashboardController extends Controller
{
    /** GET /admin */
    public function index(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);

        $contents = new ContentRepository();
        $news = new NewsRepository();
        $ecosystems = new EcosystemRepository();
        $species = new SpeciesRepository();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Administración',
            'stats'     => [
                'contenidos' => $contents->countAll(),
                'noticias'   => $news->countAll(),
                'ecosistemas' => $ecosystems->countAll(),
                'especies' => $species->countAll(),
            ],
        ], 'admin');
    }
}
