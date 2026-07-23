<?php
/**
 * Admin/SpeciesController.php — CRUD de especies con autoría.
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\EcosystemRepository;
use App\Repositories\SpeciesRepository;
use App\Services\SpeciesService;

class SpeciesController extends Controller
{
    public function __construct(
        private ?SpeciesRepository $species = null,
        private ?EcosystemRepository $ecosystems = null,
        private ?SpeciesService $service = null
    ) {
        $this->species ??= new SpeciesRepository();
        $this->ecosystems ??= new EcosystemRepository();
        $this->service ??= new SpeciesService($this->species, $this->ecosystems);
    }

    public function index(): void
    {
        $this->guard();
        $this->render('admin/especies/index', [
            'pageTitle' => 'Especies',
            'items' => $this->species->listAll(),
        ], 'admin');
    }

    public function create(): void
    {
        $this->guard();
        deny_unless(can_manage_species(), 'No tienes permiso para crear especies.', '/admin/especies');
        clear_old();
        $this->renderForm(null, [], url('/admin/especies'), 'Nueva especie');
    }

    public function store(): void
    {
        $this->guard();
        require_csrf('/admin/especies/crear');
        deny_unless(can_manage_species(), 'No tienes permiso para crear especies.', '/admin/especies');

        $input = $this->input();
        $result = $this->service->create(
            $input,
            $_FILES['imagen'] ?? null,
            (int) current_user()['id']
        );

        if (!$result['ok']) {
            flash_old($input);
            $this->renderForm(null, $result['errors'] ?? [], url('/admin/especies'), 'Nueva especie');
            clear_old();
            return;
        }

        flash('success', 'Especie creada correctamente.');
        $this->redirect('/admin/especies');
    }

    public function edit(string $id): void
    {
        $this->guard();
        $item = $this->findOrRedirect((int) $id);
        $this->authorizeItem($item, 'editar');
        clear_old();
        $this->renderForm($item, [], url('/admin/especies/' . $item['id']), 'Editar especie');
    }

    public function update(string $id): void
    {
        $this->guard();
        $id = (int) $id;
        require_csrf('/admin/especies/' . $id . '/editar');
        $item = $this->findOrRedirect($id);
        $this->authorizeItem($item, 'editar');

        $input = $this->input();
        $result = $this->service->update($id, $input, $_FILES['imagen'] ?? null);

        if (!$result['ok']) {
            flash_old($input);
            $this->renderForm(
                $item,
                $result['errors'] ?? [],
                url('/admin/especies/' . $id),
                'Editar especie'
            );
            clear_old();
            return;
        }

        flash('success', 'Especie actualizada.');
        $this->redirect('/admin/especies');
    }

    public function destroy(string $id): void
    {
        $this->guard();
        require_csrf('/admin/especies');
        $item = $this->findOrRedirect((int) $id);
        $this->authorizeItem($item, 'eliminar');

        if ($this->service->delete((int) $id)) {
            flash('success', 'Especie eliminada.');
        } else {
            flash('error', 'No se pudo eliminar la especie.');
        }
        $this->redirect('/admin/especies');
    }

    private function guard(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
    }

    private function authorizeItem(array $item, string $action): void
    {
        deny_unless(
            can_manage_species($item),
            "Solo puedes {$action} especies de tu autoría.",
            '/admin/especies'
        );
    }

    private function findOrRedirect(int $id): array
    {
        $item = $this->species->findById($id);
        if (!$item) {
            flash('error', 'Especie no encontrada.');
            $this->redirect('/admin/especies');
        }
        return $item;
    }

    private function renderForm(?array $item, array $errors, string $action, string $title): void
    {
        $options = is_admin()
            ? $this->ecosystems->listAll()
            : $this->ecosystems->listPublishedOptions();

        $this->render('admin/especies/form', [
            'pageTitle' => $title,
            'item' => $item,
            'errors' => $errors,
            'action' => $action,
            'ecosystems' => $options,
            'states' => SpeciesService::CONSERVATION_STATES,
        ], 'admin');
    }

    private function input(): array
    {
        return [
            'ecosistema_id' => $_POST['ecosistema_id'] ?? '',
            'nombre_comun' => trim($_POST['nombre_comun'] ?? ''),
            'nombre_cientifico' => trim($_POST['nombre_cientifico'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'clasificacion' => trim($_POST['clasificacion'] ?? ''),
            'habitat' => trim($_POST['habitat'] ?? ''),
            'distribucion' => trim($_POST['distribucion'] ?? ''),
            'amenazas' => trim($_POST['amenazas'] ?? ''),
            'estado_conservacion' => trim($_POST['estado_conservacion'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'publicado' => !empty($_POST['publicado']) ? '1' : '',
            'eliminar_imagen' => !empty($_POST['eliminar_imagen']) ? '1' : '',
        ];
    }
}
