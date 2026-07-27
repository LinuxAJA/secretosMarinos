<?php
/**
 * ============================================================================
 * Admin/DashboardController.php — Panel de administración
 * ============================================================================
 * Acceso: admin y docente.
 * Agrega métricas de Pasos 3–5 (educación, catálogo y participación).
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\CampaignRepository;
use App\Repositories\ContentRepository;
use App\Repositories\EcosystemRepository;
use App\Repositories\NewsRepository;
use App\Repositories\ReportRepository;
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
        $campaigns = new CampaignRepository();
        $reports = new ReportRepository();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Administración',
            'stats'     => [
                'contenidos' => $contents->countAll(),
                'noticias'   => $news->countAll(),
                'ecosistemas' => $ecosystems->countAll(),
                'especies' => $species->countAll(),
                'campanias' => $campaigns->countAll(),
                'reportes' => $reports->countAll(),
                'reportes_pendientes' => $reports->countByEstado('pendiente'),
            ],
        ], 'admin');
    }
}
