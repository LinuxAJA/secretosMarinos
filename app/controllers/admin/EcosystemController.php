<?php
/**
 * Admin/EcosystemController.php — CRUD de ecosistemas.
 * Admin: CRUD; docente: listado de solo lectura.
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\EcosystemRepository;
use App\Services\EcosystemService;

class EcosystemController extends Controller
{
    public function __construct(
        private ?EcosystemRepository $ecosystems = null,
        private ?EcosystemService $service = null
    ) {
        $this->ecosystems ??= new EcosystemRepository();
        $this->service ??= new EcosystemService($this->ecosystems);
    }

    public function index(): void
    {
        $this->guardView();
        $this->render('admin/ecosistemas/index', [
            'pageTitle' => 'Ecosistemas',
            'items' => $this->ecosystems->listAll(),
        ], 'admin');
    }

    public function create(): void
    {
        $this->guardManage();
        clear_old();
        $this->renderForm(null, [], url('/admin/ecosistemas'), 'Nuevo ecosistema');
    }

    public function store(): void
    {
        $this->guardManage();
        require_csrf('/admin/ecosistemas/crear');
        $input = $this->input();
        $result = $this->service->create($input, $_FILES['imagen'] ?? null);

        if (!$result['ok']) {
            flash_old($input);
            $this->renderForm(null, $result['errors'] ?? [], url('/admin/ecosistemas'), 'Nuevo ecosistema');
            clear_old();
            return;
        }

        flash('success', 'Ecosistema creado correctamente.');
        $this->redirect('/admin/ecosistemas');
    }

    public function edit(string $id): void
    {
        $this->guardManage();
        $item = $this->findOrRedirect((int) $id);
        clear_old();
        $this->renderForm(
            $item,
            [],
            url('/admin/ecosistemas/' . $item['id']),
            'Editar ecosistema'
        );
    }

    public function update(string $id): void
    {
        $this->guardManage();
        $id = (int) $id;
        require_csrf('/admin/ecosistemas/' . $id . '/editar');
        $item = $this->findOrRedirect($id);
        $input = $this->input();
        $result = $this->service->update($id, $input, $_FILES['imagen'] ?? null);

        if (!$result['ok']) {
            flash_old($input);
            $this->renderForm(
                $item,
                $result['errors'] ?? [],
                url('/admin/ecosistemas/' . $id),
                'Editar ecosistema'
            );
            clear_old();
            return;
        }

        flash('success', 'Ecosistema actualizado.');
        $this->redirect('/admin/ecosistemas');
    }

    public function destroy(string $id): void
    {
        $this->guardManage();
        require_csrf('/admin/ecosistemas');
        $item = $this->findOrRedirect((int) $id);
        $count = (int) $item['total_especies'];

        if ($this->service->delete((int) $id)) {
            $message = 'Ecosistema eliminado.';
            if ($count > 0) {
                $message .= " {$count} especie(s) quedaron sin ecosistema asociado.";
            }
            flash('success', $message);
        } else {
            flash('error', 'No se pudo eliminar el ecosistema.');
        }
        $this->redirect('/admin/ecosistemas');
    }

    private function guardView(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
    }

    private function guardManage(): void
    {
        $this->guardView();
        deny_unless(
            can_manage_ecosystems(),
            'Solo el administrador puede gestionar ecosistemas.',
            '/admin/ecosistemas'
        );
    }

    private function findOrRedirect(int $id): array
    {
        $item = $this->ecosystems->findById($id);
        if (!$item) {
            flash('error', 'Ecosistema no encontrado.');
            $this->redirect('/admin/ecosistemas');
        }
        return $item;
    }

    private function renderForm(?array $item, array $errors, string $action, string $title): void
    {
        $this->render('admin/ecosistemas/form', [
            'pageTitle' => $title,
            'item' => $item,
            'errors' => $errors,
            'action' => $action,
        ], 'admin');
    }

    private function input(): array
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'funcion_ecologica' => trim($_POST['funcion_ecologica'] ?? ''),
            'amenazas' => trim($_POST['amenazas'] ?? ''),
            'buenas_practicas' => trim($_POST['buenas_practicas'] ?? ''),
            'publicado' => !empty($_POST['publicado']) ? '1' : '',
            'eliminar_imagen' => !empty($_POST['eliminar_imagen']) ? '1' : '',
        ];
    }
}
