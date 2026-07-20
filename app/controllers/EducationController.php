<?php
/**
 * ============================================================================
 * EducationController.php — Biblioteca educativa (público)
 * ============================================================================
 * Flujo:
 *   GET /educacion          → listado + filtros
 *   GET /educacion/{slug}   → ficha / lectura + suma visitas
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\ContentCategoryRepository;
use App\Repositories\ContentRepository;

class EducationController extends Controller
{
    private ContentRepository $contents;
    private ContentCategoryRepository $categories;

    public function __construct()
    {
        $this->contents = new ContentRepository();
        $this->categories = new ContentCategoryRepository();
    }

    /** GET /educacion */
    public function index(): void
    {
        $categoria = trim($_GET['categoria'] ?? '');
        $q = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $catSlug = $categoria !== '' ? $categoria : null;
        $query = $q !== '' ? $q : null;

        $total = $this->contents->countPublished($catSlug, $query);
        $items = $this->contents->listPublished($catSlug, $query, $perPage, $offset);
        $pages = max(1, (int) ceil($total / $perPage));

        $this->render('pages/educacion/index', [
            'pageTitle'  => 'Biblioteca educativa',
            'items'      => $items,
            'categories' => $this->categories->all(),
            'filters'    => ['categoria' => $categoria, 'q' => $q],
            'pagination' => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    /** GET /educacion/{slug} */
    public function show(string $slug): void
    {
        $item = $this->contents->findPublishedBySlug($slug);
        if (!$item) {
            flash('error', 'Contenido no encontrado o no publicado.');
            $this->redirect('/educacion');
        }

        $this->contents->incrementViews((int) $item['id']);
        $item['visitas'] = (int) $item['visitas'] + 1;

        $this->render('pages/educacion/show', [
            'pageTitle' => $item['titulo'],
            'item'      => $item,
        ]);
    }
}
