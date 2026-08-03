<?php
/**
 * ============================================================================
 * Admin/StatsController.php — Vista de estadísticas básicas (Paso 7)
 * ============================================================================
 * Acceso: admin y docente (can_view_stats).
 * El payload ya viene filtrado por StatsService (Opción A: sin Comunidad para docente).
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Services\StatsService;

class StatsController extends Controller
{
    public function __construct(private ?StatsService $stats = null)
    {
        $this->stats ??= new StatsService();
    }

    /** GET /admin/estadisticas */
    public function index(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
        deny_unless(
            can_view_stats(),
            'No tienes permiso para ver estadísticas.',
            '/admin'
        );

        $this->render('admin/estadisticas/index', [
            'pageTitle' => 'Estadísticas',
            'stats' => $this->stats->forCurrentUser(),
        ], 'admin');
    }
}
