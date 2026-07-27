<?php
/**
 * ============================================================================
 * Admin/CampaignController.php — CRUD administrativo de campañas
 * ============================================================================
 * Acceso al panel: admin y docente.
 * Mutaciones: admin global; docente solo campañas de su responsabilidad.
 * Incluye la regla de motivo obligatorio al cancelar (validada en el Service).
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\CampaignRepository;
use App\Services\CampaignService;

class CampaignController extends Controller
{
    public function __construct(
        private ?CampaignRepository $campaigns = null,
        private ?CampaignService $service = null
    ) {
        $this->campaigns ??= new CampaignRepository();
        $this->service ??= new CampaignService($this->campaigns);
    }

    /** GET /admin/campanias */
    public function index(): void
    {
        $this->guard();
        $this->render('admin/campanias/index', [
            'pageTitle' => 'Campañas',
            'items' => $this->campaigns->listAll(),
            'states' => [
                'borrador' => 'Borrador',
                'activa' => 'Activa',
                'finalizada' => 'Finalizada',
                'cancelada' => 'Cancelada',
            ],
        ], 'admin');
    }

    /** GET /admin/campanias/crear */
    public function create(): void
    {
        $this->guard();
        deny_unless(can_manage_campaigns(), 'No tienes permiso para crear campañas.', '/admin/campanias');
        clear_old();
        $this->renderForm(null, [], url('/admin/campanias'), 'Nueva campaña');
    }

    /** POST /admin/campanias */
    public function store(): void
    {
        $this->guard();
        require_csrf('/admin/campanias/crear');
        deny_unless(can_manage_campaigns(), 'No tienes permiso para crear campañas.', '/admin/campanias');

        $input = $this->input();
        $result = $this->service->create(
            $input,
            $_FILES['imagen'] ?? null,
            (int) current_user()['id']
        );

        if (!$result['ok']) {
            flash_old($input);
            $this->renderForm(null, $result['errors'] ?? [], url('/admin/campanias'), 'Nueva campaña');
            clear_old();
            return;
        }

        flash('success', 'Campaña creada correctamente.');
        $this->redirect('/admin/campanias');
    }

    /** GET /admin/campanias/{id}/editar */
    public function edit(string $id): void
    {
        $this->guard();
        $item = $this->findOrRedirect((int) $id);
        $this->authorizeItem($item, 'editar');
        clear_old();
        $this->renderForm($item, [], url('/admin/campanias/' . $item['id']), 'Editar campaña');
    }

    /** POST /admin/campanias/{id} */
    public function update(string $id): void
    {
        $this->guard();
        $id = (int) $id;
        require_csrf('/admin/campanias/' . $id . '/editar');
        $item = $this->findOrRedirect($id);
        $this->authorizeItem($item, 'editar');

        $input = $this->input();
        $result = $this->service->update($id, $input, $_FILES['imagen'] ?? null);

        if (!$result['ok']) {
            flash_old($input);
            $this->renderForm(
                $item,
                $result['errors'] ?? [],
                url('/admin/campanias/' . $id),
                'Editar campaña'
            );
            clear_old();
            return;
        }

        flash('success', 'Campaña actualizada.');
        $this->redirect('/admin/campanias');
    }

    /** POST /admin/campanias/{id}/eliminar */
    public function destroy(string $id): void
    {
        $this->guard();
        require_csrf('/admin/campanias');
        $item = $this->findOrRedirect((int) $id);
        $this->authorizeItem($item, 'eliminar');

        if ($this->service->delete((int) $id)) {
            flash('success', 'Campaña eliminada.');
        } else {
            flash('error', 'No se pudo eliminar la campaña.');
        }
        $this->redirect('/admin/campanias');
    }

    /** Acceso al módulo admin: admin o docente. */
    private function guard(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
    }

    /**
     * Verifica ownership / privilegio admin sobre la campaña.
     *
     * @param array<string,mixed> $item
     */
    private function authorizeItem(array $item, string $action): void
    {
        deny_unless(
            can_manage_campaigns($item),
            "Solo puedes {$action} campañas de tu responsabilidad.",
            '/admin/campanias'
        );
    }

    /** @return array<string,mixed> */
    private function findOrRedirect(int $id): array
    {
        $item = $this->campaigns->findById($id);
        if (!$item) {
            flash('error', 'Campaña no encontrada.');
            $this->redirect('/admin/campanias');
        }
        return $item;
    }

    /**
     * @param array<string,mixed>|null $item
     * @param array<string,string>     $errors
     */
    private function renderForm(?array $item, array $errors, string $action, string $title): void
    {
        $this->render('admin/campanias/form', [
            'pageTitle' => $title,
            'item' => $item,
            'errors' => $errors,
            'action' => $action,
            'states' => [
                'borrador' => 'Borrador',
                'activa' => 'Activa',
                'finalizada' => 'Finalizada',
                'cancelada' => 'Cancelada',
            ],
        ], 'admin');
    }

    /**
     * @return array<string,string>
     */
    private function input(): array
    {
        return [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'objetivo' => trim($_POST['objetivo'] ?? ''),
            'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
            'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'borrador'),
            'motivo_cancelacion' => trim($_POST['motivo_cancelacion'] ?? ''),
            'eliminar_imagen' => !empty($_POST['eliminar_imagen']) ? '1' : '',
        ];
    }
}
