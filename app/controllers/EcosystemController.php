<?php
/**
 * Catálogo público de ecosistemas.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\EcosystemRepository;
use App\Repositories\SpeciesRepository;

class EcosystemController extends Controller
{
    public function __construct(
        private ?EcosystemRepository $ecosystems = null,
        private ?SpeciesRepository $species = null
    ) {
        $this->ecosystems ??= new EcosystemRepository();
        $this->species ??= new SpeciesRepository();
    }

    /** GET /ecosistemas */
    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;
        $query = $q !== '' ? $q : null;
        $total = $this->ecosystems->countPublished($query);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $this->render('pages/ecosistemas/index', [
            'pageTitle' => 'Ecosistemas marinos',
            'items' => $this->ecosystems->listPublished(
                $query,
                $perPage,
                ($page - 1) * $perPage
            ),
            'filters' => ['q' => $q],
            'pagination' => compact('page', 'pages', 'total'),
        ]);
    }

    /** GET /ecosistemas/{slug} */
    public function show(string $slug): void
    {
        $item = $this->ecosystems->findPublishedBySlug($slug);
        if (!$item) {
            flash('error', 'Ecosistema no encontrado o no publicado.');
            $this->redirect('/ecosistemas');
        }

        $this->render('pages/ecosistemas/show', [
            'pageTitle' => $item['nombre'],
            'item' => $item,
            'species' => $this->species->listPublishedByEcosystem((int) $item['id']),
        ]);
    }
}
