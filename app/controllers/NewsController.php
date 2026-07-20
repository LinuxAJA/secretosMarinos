<?php
/**
 * ============================================================================
 * NewsController.php — Noticias ambientales (público)
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\NewsRepository;

class NewsController extends Controller
{
    private NewsRepository $news;

    public function __construct()
    {
        $this->news = new NewsRepository();
    }

    /** GET /noticias */
    public function index(): void
    {
        $categoria = trim($_GET['categoria'] ?? '');
        $q = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $cat = $categoria !== '' ? $categoria : null;
        $query = $q !== '' ? $q : null;

        $total = $this->news->countPublished($cat, $query);
        $items = $this->news->listPublished($cat, $query, $perPage, $offset);
        $featured = $this->news->listFeatured(3);
        $pages = max(1, (int) ceil($total / $perPage));

        $this->render('pages/noticias/index', [
            'pageTitle'  => 'Noticias',
            'items'      => $items,
            'featured'   => $featured,
            'categories' => $this->news->distinctCategories(),
            'filters'    => ['categoria' => $categoria, 'q' => $q],
            'pagination' => ['page' => $page, 'pages' => $pages, 'total' => $total],
        ]);
    }

    /** GET /noticias/{slug} */
    public function show(string $slug): void
    {
        $item = $this->news->findPublishedBySlug($slug);
        if (!$item) {
            flash('error', 'Noticia no encontrada o no publicada.');
            $this->redirect('/noticias');
        }

        $this->render('pages/noticias/show', [
            'pageTitle' => $item['titulo'],
            'item'      => $item,
        ]);
    }
}
