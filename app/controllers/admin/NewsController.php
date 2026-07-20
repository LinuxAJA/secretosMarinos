<?php
/**
 * ============================================================================
 * Admin/NewsController.php — CRUD de noticias
 * ============================================================================
 * RBAC: igual que contenidos (admin global / docente solo autoría).
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\NewsRepository;
use App\Services\NewsService;

class NewsController extends Controller
{
    private NewsRepository $news;
    private NewsService $service;

    public function __construct()
    {
        $this->news = new NewsRepository();
        $this->service = new NewsService($this->news);
    }

    private function guard(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
    }

    /** GET /admin/noticias */
    public function index(): void
    {
        $this->guard();
        $this->render('admin/noticias/index', [
            'pageTitle' => 'Noticias',
            'items'     => $this->news->listAll(),
        ], 'admin');
    }

    /** GET /admin/noticias/crear */
    public function create(): void
    {
        $this->guard();
        deny_unless(
            can_manage_news(null),
            'No tienes permiso para crear noticias.',
            '/admin/noticias'
        );

        clear_old();
        $this->render('admin/noticias/form', [
            'pageTitle' => 'Nueva noticia',
            'item'      => null,
            'errors'    => [],
            'action'    => url('/admin/noticias'),
        ], 'admin');
    }

    /** POST /admin/noticias */
    public function store(): void
    {
        $this->guard();
        require_csrf('/admin/noticias/crear');
        deny_unless(
            can_manage_news(null),
            'No tienes permiso para crear noticias.',
            '/admin/noticias'
        );

        $input = $this->inputFromPost();
        $result = $this->service->create($input, (int) current_user()['id']);

        if (!$result['ok']) {
            flash_old($input);
            $this->render('admin/noticias/form', [
                'pageTitle' => 'Nueva noticia',
                'item'      => null,
                'errors'    => $result['errors'] ?? [],
                'action'    => url('/admin/noticias'),
            ], 'admin');
            clear_old();
            return;
        }

        flash('success', 'Noticia creada.');
        $this->redirect('/admin/noticias');
    }

    /** GET /admin/noticias/{id}/editar */
    public function edit(string $id): void
    {
        $this->guard();
        $item = $this->news->findById((int) $id);
        if (!$item) {
            flash('error', 'Noticia no encontrada.');
            $this->redirect('/admin/noticias');
        }

        deny_unless(
            can_manage_news($item),
            'Solo puedes editar noticias de tu autoría.',
            '/admin/noticias'
        );

        clear_old();
        $this->render('admin/noticias/form', [
            'pageTitle' => 'Editar noticia',
            'item'      => $item,
            'errors'    => [],
            'action'    => url('/admin/noticias/' . $item['id']),
        ], 'admin');
    }

    /** POST /admin/noticias/{id} */
    public function update(string $id): void
    {
        $this->guard();
        $idInt = (int) $id;
        require_csrf('/admin/noticias/' . $idInt . '/editar');

        $item = $this->news->findById($idInt);
        if (!$item) {
            flash('error', 'Noticia no encontrada.');
            $this->redirect('/admin/noticias');
        }

        deny_unless(
            can_manage_news($item),
            'Solo puedes editar noticias de tu autoría.',
            '/admin/noticias'
        );

        $input = $this->inputFromPost();
        $result = $this->service->update($idInt, $input);

        if (!$result['ok']) {
            flash_old($input);
            $this->render('admin/noticias/form', [
                'pageTitle' => 'Editar noticia',
                'item'      => $item,
                'errors'    => $result['errors'] ?? [],
                'action'    => url('/admin/noticias/' . $idInt),
            ], 'admin');
            clear_old();
            return;
        }

        flash('success', 'Noticia actualizada.');
        $this->redirect('/admin/noticias');
    }

    /** POST /admin/noticias/{id}/eliminar */
    public function destroy(string $id): void
    {
        $this->guard();
        require_csrf('/admin/noticias');

        $item = $this->news->findById((int) $id);
        if (!$item) {
            flash('error', 'Noticia no encontrada.');
            $this->redirect('/admin/noticias');
        }

        deny_unless(
            can_manage_news($item),
            'Solo puedes eliminar noticias de tu autoría.',
            '/admin/noticias'
        );

        if ($this->news->delete((int) $id)) {
            flash('success', 'Noticia eliminada.');
        } else {
            flash('error', 'No se pudo eliminar la noticia.');
        }
        $this->redirect('/admin/noticias');
    }

    /** @return array<string,mixed> */
    private function inputFromPost(): array
    {
        return [
            'titulo'    => trim($_POST['titulo'] ?? ''),
            'slug'      => trim($_POST['slug'] ?? ''),
            'resumen'   => trim($_POST['resumen'] ?? ''),
            'cuerpo'    => trim($_POST['cuerpo'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'publicada' => !empty($_POST['publicada']) ? '1' : '',
            'destacada' => !empty($_POST['destacada']) ? '1' : '',
        ];
    }
}
