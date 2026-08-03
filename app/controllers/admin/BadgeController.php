<?php
/**
 * ============================================================================
 * Admin/BadgeController.php — CRUD de insignias
 * ============================================================================
 * Listado: admin y docente (docente solo lectura).
 * Mutaciones: solo admin (can_manage_badges).
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\BadgeRepository;
use App\Services\BadgeService;

class BadgeController extends Controller
{
    public function __construct(
        private ?BadgeRepository $badges = null,
        private ?BadgeService $service = null
    ) {
        $this->badges ??= new BadgeRepository();
        $this->service ??= new BadgeService($this->badges);
    }

    public function index(): void
    {
        $this->guardView();
        $this->render('admin/insignias/index', [
            'pageTitle' => 'Insignias',
            'items' => $this->badges->listAll(false),
        ], 'admin');
    }

    public function create(): void
    {
        $this->guardManage();
        clear_old();
        $this->renderForm(null, [], url('/admin/insignias'), 'Nueva insignia');
    }

    public function store(): void
    {
        $this->guardManage();
        require_csrf('/admin/insignias/crear');
        $input = $this->input();
        $result = $this->service->create($input);

        if (!$result['ok']) {
            flash_old($input);
            $this->renderForm(null, $result['errors'] ?? [], url('/admin/insignias'), 'Nueva insignia');
            clear_old();
            return;
        }

        flash('success', 'Insignia creada.');
        $this->redirect('/admin/insignias');
    }

    public function edit(string $id): void
    {
        $this->guardManage();
        $item = $this->findOrRedirect((int) $id);
        clear_old();
        $this->renderForm($item, [], url('/admin/insignias/' . $item['id']), 'Editar insignia');
    }

    public function update(string $id): void
    {
        $this->guardManage();
        $id = (int) $id;
        require_csrf('/admin/insignias/' . $id . '/editar');
        $item = $this->findOrRedirect($id);
        $input = $this->input();
        $result = $this->service->update($id, $input);

        if (!$result['ok']) {
            flash_old($input);
            $this->renderForm($item, $result['errors'] ?? [], url('/admin/insignias/' . $id), 'Editar insignia');
            clear_old();
            return;
        }

        flash('success', 'Insignia actualizada.');
        $this->redirect('/admin/insignias');
    }

    public function destroy(string $id): void
    {
        $this->guardManage();
        require_csrf('/admin/insignias');
        $this->findOrRedirect((int) $id);

        if ($this->service->delete((int) $id)) {
            flash('success', 'Insignia eliminada.');
        } else {
            flash('error', 'No se pudo eliminar la insignia.');
        }
        $this->redirect('/admin/insignias');
    }

    private function guardView(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
    }

    private function guardManage(): void
    {
        $this->guardView();
        deny_unless(
            can_manage_badges(),
            'Solo el administrador puede gestionar insignias.',
            '/admin/insignias'
        );
    }

    /** @return array<string,mixed> */
    private function findOrRedirect(int $id): array
    {
        $item = $this->badges->findById($id);
        if (!$item) {
            flash('error', 'Insignia no encontrada.');
            $this->redirect('/admin/insignias');
        }
        return $item;
    }

    /**
     * @param array<string,mixed>|null $item
     * @param array<string,string>     $errors
     */
    private function renderForm(?array $item, array $errors, string $action, string $title): void
    {
        $this->render('admin/insignias/form', [
            'pageTitle' => $title,
            'item' => $item,
            'errors' => $errors,
            'action' => $action,
        ], 'admin');
    }

    /** @return array<string,string> */
    private function input(): array
    {
        return [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'icono' => trim($_POST['icono'] ?? ''),
            'puntos_requeridos' => trim($_POST['puntos_requeridos'] ?? '0'),
            'activa' => !empty($_POST['activa']) ? '1' : '',
        ];
    }
}
