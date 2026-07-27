<?php
/**
 * ============================================================================
 * CampaignController.php — Catálogo público de campañas ambientales
 * ============================================================================
 * Expone listado y ficha. Solo muestra campañas activa/finalizada.
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\CampaignRepository;

class CampaignController extends Controller
{
    public function __construct(
        private ?CampaignRepository $campaigns = null
    ) {
        $this->campaigns ??= new CampaignRepository();
    }

    /**
     * GET /campanias — Listado público con búsqueda y filtro de estado.
     */
    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $estado = trim($_GET['estado'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;

        $query = $q !== '' ? $q : null;
        $state = in_array($estado, ['activa', 'finalizada'], true) ? $estado : null;

        $total = $this->campaigns->countPublic($query, $state);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $this->render('pages/campanias/index', [
            'pageTitle' => 'Campañas ambientales',
            'items' => $this->campaigns->listPublic(
                $query,
                $state,
                $perPage,
                ($page - 1) * $perPage
            ),
            'filters' => ['q' => $q, 'estado' => $estado],
            'states' => ['activa' => 'Activa', 'finalizada' => 'Finalizada'],
            'pagination' => compact('page', 'pages', 'total'),
        ]);
    }

    /**
     * GET /campanias/{slug} — Ficha pública.
     *
     * @param string $slug Identificador amigable de la campaña
     */
    public function show(string $slug): void
    {
        $item = $this->campaigns->findPublicBySlug($slug);
        if (!$item) {
            flash('error', 'Campaña no encontrada o no disponible públicamente.');
            $this->redirect('/campanias');
        }

        $this->render('pages/campanias/show', [
            'pageTitle' => $item['titulo'],
            'item' => $item,
            'allStates' => [
                'borrador' => 'Borrador',
                'activa' => 'Activa',
                'finalizada' => 'Finalizada',
                'cancelada' => 'Cancelada',
            ],
        ]);
    }
}
