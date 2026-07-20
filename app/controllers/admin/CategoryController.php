<?php
/**
 * ============================================================================
 * Admin/CategoryController.php — Categorías educativas
 * ============================================================================
 * RBAC:
 *   - Listar: admin y docente (docente solo lectura)
 *   - Crear / editar / eliminar: solo admin
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\ContentCategoryRepository;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    private ContentCategoryRepository $categories;
    private CategoryService $service;

    public function __construct()
    {
        $this->categories = new ContentCategoryRepository();
        $this->service = new CategoryService($this->categories);
    }

    /** Acceso al módulo (ver listado). */
    private function guardView(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
    }

    /** Mutaciones solo administrador. */
    private function guardManage(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
        deny_unless(
            can_manage_categories(),
            'Solo el administrador puede gestionar categorías.',
            '/admin/categorias'
        );
    }

    /** GET /admin/categorias */
    public function index(): void
    {
        $this->guardView();
        $this->render('admin/categorias/index', [
            'pageTitle' => 'Categorías',
            'items'     => $this->categories->all(),
        ], 'admin');
    }

    /** GET /admin/categorias/crear */
    public function create(): void
    {
        $this->guardManage();
        clear_old();
        $this->render('admin/categorias/form', [
            'pageTitle' => 'Nueva categoría',
            'item'      => null,
            'errors'    => [],
            'action'    => url('/admin/categorias'),
        ], 'admin');
    }

    /** POST /admin/categorias */
    public function store(): void
    {
        $this->guardManage();
        require_csrf('/admin/categorias/crear');

        $input = [
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'slug'        => trim($_POST['slug'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        $result = $this->service->create($input);
        if (!$result['ok']) {
            flash_old($input);
            $this->render('admin/categorias/form', [
                'pageTitle' => 'Nueva categoría',
                'item'      => null,
                'errors'    => $result['errors'] ?? [],
                'action'    => url('/admin/categorias'),
            ], 'admin');
            clear_old();
            return;
        }

        flash('success', 'Categoría creada.');
        $this->redirect('/admin/categorias');
    }

    /** GET /admin/categorias/{id}/editar */
    public function edit(string $id): void
    {
        $this->guardManage();
        $item = $this->categories->findById((int) $id);
        if (!$item) {
            flash('error', 'Categoría no encontrada.');
            $this->redirect('/admin/categorias');
        }

        clear_old();
        $this->render('admin/categorias/form', [
            'pageTitle' => 'Editar categoría',
            'item'      => $item,
            'errors'    => [],
            'action'    => url('/admin/categorias/' . $item['id']),
        ], 'admin');
    }

    /** POST /admin/categorias/{id} */
    public function update(string $id): void
    {
        $this->guardManage();
        $idInt = (int) $id;
        require_csrf('/admin/categorias/' . $idInt . '/editar');

        $item = $this->categories->findById($idInt);
        if (!$item) {
            flash('error', 'Categoría no encontrada.');
            $this->redirect('/admin/categorias');
        }

        $input = [
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'slug'        => trim($_POST['slug'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        $result = $this->service->update($idInt, $input);
        if (!$result['ok']) {
            flash_old($input);
            $this->render('admin/categorias/form', [
                'pageTitle' => 'Editar categoría',
                'item'      => array_merge($item, $input),
                'errors'    => $result['errors'] ?? [],
                'action'    => url('/admin/categorias/' . $idInt),
            ], 'admin');
            clear_old();
            return;
        }

        flash('success', 'Categoría actualizada.');
        $this->redirect('/admin/categorias');
    }

    /** POST /admin/categorias/{id}/eliminar */
    public function destroy(string $id): void
    {
        $this->guardManage();
        require_csrf('/admin/categorias');

        // ON DELETE SET NULL: contenidos quedan sin categoría, no se borran
        if ($this->categories->delete((int) $id)) {
            flash('success', 'Categoría eliminada.');
        } else {
            flash('error', 'No se pudo eliminar la categoría.');
        }
        $this->redirect('/admin/categorias');
    }
}
