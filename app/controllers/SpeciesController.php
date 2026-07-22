<?php
/**
 * Catálogo público de especies marinas.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\EcosystemRepository;
use App\Repositories\SpeciesRepository;
use App\Services\SpeciesService;

class SpeciesController extends Controller
{
    public function __construct(
        private ?SpeciesRepository $species = null,
        private ?EcosystemRepository $ecosystems = null
    ) {
        $this->species ??= new SpeciesRepository();
        $this->ecosystems ??= new EcosystemRepository();
    }

    /** GET /especies */
    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $ecosystemId = (int) ($_GET['ecosistema'] ?? 0);
        $conservation = trim($_GET['conservacion'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;

        $query = $q !== '' ? $q : null;
        $ecosystem = $ecosystemId > 0 ? $ecosystemId : null;
        $state = in_array($conservation, SpeciesService::CONSERVATION_STATES, true)
            ? $conservation
            : null;

        $total = $this->species->countPublished($query, $ecosystem, $state);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $this->render('pages/especies/index', [
            'pageTitle' => 'Especies marinas',
            'items' => $this->species->listPublished(
                $query,
                $ecosystem,
                $state,
                $perPage,
                ($page - 1) * $perPage
            ),
            'ecosystems' => $this->ecosystems->listPublishedOptions(),
            'states' => SpeciesService::CONSERVATION_STATES,
            'filters' => [
                'q' => $q,
                'ecosistema' => $ecosystemId,
                'conservacion' => $conservation,
            ],
            'pagination' => compact('page', 'pages', 'total'),
        ]);
    }

    /** GET /especies/{slug} */
    public function show(string $slug): void
    {
        $item = $this->species->findPublishedBySlug($slug);
        if (!$item) {
            flash('error', 'Especie no encontrada o no publicada.');
            $this->redirect('/especies');
        }

        $this->render('pages/especies/show', [
            'pageTitle' => $item['nombre_comun'],
            'item' => $item,
        ]);
    }
}
