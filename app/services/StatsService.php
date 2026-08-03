<?php
/**
 * ============================================================================
 * StatsService.php — KPIs básicos del panel administrativo (Paso 7)
 * ============================================================================
 * Agrega conteos on-the-fly desde repositorios existentes.
 * No persiste estadísticas ni registra visitas (eso queda fuera de V1.0).
 *
 * Opción A (RBAC):
 *   - Admin: ve todos los bloques, incluido "comunidad" (cuentas/roles).
 *   - Docente: ve educación, catálogo, participación y gamificación;
 *     no recibe el bloque comunidad.
 * ============================================================================
 */

namespace App\Services;

use App\Repositories\BadgeRepository;
use App\Repositories\CampaignRepository;
use App\Repositories\ContentRepository;
use App\Repositories\EcosystemRepository;
use App\Repositories\NewsRepository;
use App\Repositories\PointsRepository;
use App\Repositories\ReportRepository;
use App\Repositories\SpeciesRepository;
use App\Repositories\UserRepository;

class StatsService
{
    public function __construct(
        private ?UserRepository $users = null,
        private ?ContentRepository $contents = null,
        private ?NewsRepository $news = null,
        private ?EcosystemRepository $ecosystems = null,
        private ?SpeciesRepository $species = null,
        private ?CampaignRepository $campaigns = null,
        private ?ReportRepository $reports = null,
        private ?BadgeRepository $badges = null,
        private ?PointsRepository $points = null
    ) {
        $this->users ??= new UserRepository();
        $this->contents ??= new ContentRepository();
        $this->news ??= new NewsRepository();
        $this->ecosystems ??= new EcosystemRepository();
        $this->species ??= new SpeciesRepository();
        $this->campaigns ??= new CampaignRepository();
        $this->reports ??= new ReportRepository();
        $this->badges ??= new BadgeRepository();
        $this->points ??= new PointsRepository();
    }

    /**
     * Arma el payload de KPIs según el rol del usuario autenticado.
     *
     * @return array<string,mixed>
     */
    public function forCurrentUser(): array
    {
        $payload = [
            'educacion' => $this->educationBlock(),
            'catalogo' => $this->catalogBlock(),
            'participacion' => $this->participationBlock(),
            'gamificacion' => $this->gamificationBlock(),
        ];

        // Bloque Comunidad: métricas de cuentas — solo administrador
        if (is_admin()) {
            $payload['comunidad'] = $this->communityBlock();
        }

        return $payload;
    }

    /**
     * @return array<string,int>
     */
    private function educationBlock(): array
    {
        return [
            'contenidos_total' => $this->contents->countAll(),
            'contenidos_publicados' => $this->contents->countPublished(),
            'noticias_total' => $this->news->countAll(),
            'noticias_publicadas' => $this->news->countPublished(),
        ];
    }

    /**
     * @return array<string,int>
     */
    private function catalogBlock(): array
    {
        return [
            'ecosistemas_total' => $this->ecosystems->countAll(),
            'ecosistemas_publicados' => $this->ecosystems->countPublished(),
            'especies_total' => $this->species->countAll(),
            'especies_publicadas' => $this->species->countPublished(),
        ];
    }

    /**
     * @return array{campanias:array<string,int>,reportes:array<string,int>,campanias_total:int,reportes_total:int}
     */
    private function participationBlock(): array
    {
        $campaignsByEstado = $this->campaigns->countGroupedByEstado();
        $reportsByEstado = $this->reports->countGroupedByEstado();

        return [
            'campanias_total' => $this->campaigns->countAll(),
            'campanias' => [
                'activa' => (int) ($campaignsByEstado['activa'] ?? 0),
                'finalizada' => (int) ($campaignsByEstado['finalizada'] ?? 0),
                'cancelada' => (int) ($campaignsByEstado['cancelada'] ?? 0),
            ],
            'reportes_total' => $this->reports->countAll(),
            'reportes' => [
                'pendiente' => (int) ($reportsByEstado['pendiente'] ?? 0),
                'en_revision' => (int) ($reportsByEstado['en_revision'] ?? 0),
                'resuelto' => (int) ($reportsByEstado['resuelto'] ?? 0),
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function gamificationBlock(): array
    {
        return [
            'insignias_catalogo' => $this->badges->countAll(),
            'insignias_otorgadas' => $this->badges->countAwards(),
            'promedio_puntos_activos' => round($this->users->averageActivePoints(), 1),
            'ranking_top' => $this->points->ranking(5),
        ];
    }

    /**
     * Métricas de cuentas (solo admin).
     *
     * @return array<string,mixed>
     */
    private function communityBlock(): array
    {
        $byRole = $this->users->countGroupedByRole();

        return [
            'usuarios_total' => $this->users->countAll(),
            'usuarios_activos' => $this->users->countActive(),
            'usuarios_inactivos' => $this->users->countInactive(),
            'por_rol' => [
                ROLE_ADMIN => (int) ($byRole[ROLE_ADMIN] ?? 0),
                ROLE_DOCENTE => (int) ($byRole[ROLE_DOCENTE] ?? 0),
                ROLE_ESTUDIANTE => (int) ($byRole[ROLE_ESTUDIANTE] ?? 0),
            ],
        ];
    }
}
