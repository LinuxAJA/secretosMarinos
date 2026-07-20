<?php
/**
 * ============================================================================
 * Admin/ContentController.php — CRUD de contenidos educativos
 * ============================================================================
 * RBAC:
 *   - Admin y docente: listar / crear
 *   - Editar / eliminar: admin cualquiera; docente solo autor_id propio
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\ContentCategoryRepository;
use App\Repositories\ContentRepository;
use App\Services\ContentService;

class ContentController extends Controller
{
    private ContentRepository $contents;
    private ContentCategoryRepository $categories;
    private ContentService $service;

    public function __construct()
    {
        $this->contents = new ContentRepository();
        $this->categories = new ContentCategoryRepository();
        $this->service = new ContentService($this->contents, $this->categories);
    }

    private function guard(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
    }

    /** GET /admin/contenidos */
    public function index(): void
    {
        $this->guard();
        $this->render('admin/contenidos/index', [
            'pageTitle' => 'Contenidos',
            'items'     => $this->contents->listAll(),
        ], 'admin');
    }

    /** GET /admin/contenidos/crear */
    public function create(): void
    {
        $this->guard();
        deny_unless(
            can_manage_content(null),
            'No tienes permiso para crear contenidos.',
            '/admin/contenidos'
        );

        clear_old();
        $this->render('admin/contenidos/form', [
            'pageTitle'  => 'Nuevo contenido',
            'item'       => null,
            'categories' => $this->categories->all(),
            'errors'     => [],
            'action'     => url('/admin/contenidos'),
        ], 'admin');
    }

    /** POST /admin/contenidos */
    public function store(): void
    {
        $this->guard();
        require_csrf('/admin/contenidos/crear');
        deny_unless(
            can_manage_content(null),
            'No tienes permiso para crear contenidos.',
            '/admin/contenidos'
        );

        $input = $this->inputFromPost();
        $result = $this->service->create($input, (int) current_user()['id']);

        if (!$result['ok']) {
            flash_old($input);
            $this->render('admin/contenidos/form', [
                'pageTitle'  => 'Nuevo contenido',
                'item'       => null,
                'categories' => $this->categories->all(),
                'errors'     => $result['errors'] ?? [],
                'action'     => url('/admin/contenidos'),
            ], 'admin');
            clear_old();
            return;
        }

        flash('success', 'Contenido creado correctamente.');
        $this->redirect('/admin/contenidos');
    }

    /** GET /admin/contenidos/{id}/editar */
    public function edit(string $id): void
    {
        $this->guard();
        $item = $this->contents->findById((int) $id);
        if (!$item) {
            flash('error', 'Contenido no encontrado.');
            $this->redirect('/admin/contenidos');
        }

        deny_unless(
            can_manage_content($item),
            'Solo puedes editar contenidos de tu autoría.',
            '/admin/contenidos'
        );

        clear_old();
        $this->render('admin/contenidos/form', [
            'pageTitle'  => 'Editar contenido',
            'item'       => $item,
            'categories' => $this->categories->all(),
            'errors'     => [],
            'action'     => url('/admin/contenidos/' . $item['id']),
        ], 'admin');
    }

    /** POST /admin/contenidos/{id} */
    public function update(string $id): void
    {
        $this->guard();
        $idInt = (int) $id;
        require_csrf('/admin/contenidos/' . $idInt . '/editar');

        $item = $this->contents->findById($idInt);
        if (!$item) {
            flash('error', 'Contenido no encontrado.');
            $this->redirect('/admin/contenidos');
        }

        deny_unless(
            can_manage_content($item),
            'Solo puedes editar contenidos de tu autoría.',
            '/admin/contenidos'
        );

        $input = $this->inputFromPost();
        $result = $this->service->update($idInt, $input);

        if (!$result['ok']) {
            flash_old($input);
            $this->render('admin/contenidos/form', [
                'pageTitle'  => 'Editar contenido',
                'item'       => $item,
                'categories' => $this->categories->all(),
                'errors'     => $result['errors'] ?? [],
                'action'     => url('/admin/contenidos/' . $idInt),
            ], 'admin');
            clear_old();
            return;
        }

        flash('success', 'Contenido actualizado.');
        $this->redirect('/admin/contenidos');
    }

    /** POST /admin/contenidos/{id}/eliminar */
    public function destroy(string $id): void
    {
        $this->guard();
        require_csrf('/admin/contenidos');

        $item = $this->contents->findById((int) $id);
        if (!$item) {
            flash('error', 'Contenido no encontrado.');
            $this->redirect('/admin/contenidos');
        }

        deny_unless(
            can_manage_content($item),
            'Solo puedes eliminar contenidos de tu autoría.',
            '/admin/contenidos'
        );

        if ($this->contents->delete((int) $id)) {
            flash('success', 'Contenido eliminado.');
        } else {
            flash('error', 'No se pudo eliminar el contenido.');
        }
        $this->redirect('/admin/contenidos');
    }

    /** @return array<string,mixed> */
    private function inputFromPost(): array
    {
        return [
            'titulo'       => trim($_POST['titulo'] ?? ''),
            'slug'         => trim($_POST['slug'] ?? ''),
            'resumen'      => trim($_POST['resumen'] ?? ''),
            'cuerpo'       => trim($_POST['cuerpo'] ?? ''),
            'nivel'        => $_POST['nivel'] ?? 'basico',
            'categoria_id' => $_POST['categoria_id'] ?? '',
            'publicado'    => !empty($_POST['publicado']) ? '1' : '',
        ];
    }
}
