<?php
/**
 * ============================================================================
 * Admin/DashboardController.php — Panel de administración
 * ============================================================================
 * Acceso: admin y docente.
 * Paso 7: reutiliza StatsService para KPIs resumidos y accesos rápidos
 * a usuarios (solo admin) y a la vista de estadísticas.
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Services\StatsService;

class DashboardController extends Controller
{
    public function __construct(private ?StatsService $stats = null)
    {
        $this->stats ??= new StatsService();
    }

    /** GET /admin */
    public function index(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);

        $kpis = $this->stats->forCurrentUser();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Administración',
            'kpis' => $kpis,
            // Compatibilidad con tarjetas existentes (conteos planos)
            'stats' => [
                'contenidos' => (int) ($kpis['educacion']['contenidos_total'] ?? 0),
                'noticias' => (int) ($kpis['educacion']['noticias_total'] ?? 0),
                'ecosistemas' => (int) ($kpis['catalogo']['ecosistemas_total'] ?? 0),
                'especies' => (int) ($kpis['catalogo']['especies_total'] ?? 0),
                'campanias' => (int) ($kpis['participacion']['campanias_total'] ?? 0),
                'reportes' => (int) ($kpis['participacion']['reportes_total'] ?? 0),
                'reportes_pendientes' => (int) ($kpis['participacion']['reportes']['pendiente'] ?? 0),
                'insignias' => (int) ($kpis['gamificacion']['insignias_catalogo'] ?? 0),
                'usuarios' => (int) ($kpis['comunidad']['usuarios_total'] ?? 0),
            ],
        ], 'admin');
    }
}
