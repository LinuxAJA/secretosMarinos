<?php
/**
 * ============================================================================
 * Admin/UserController.php — Gestión administrativa de usuarios (Paso 7)
 * ============================================================================
 * Solo administrador:
 *   - Listar / filtrar cuentas
 *   - Ver detalle (solo lectura de datos personales)
 *   - Editar rol y estado activo
 *
 * No permite editar nombre, correo ni contraseña desde aquí.
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\UserRepository;
use App\Services\UserAdminService;

class UserController extends Controller
{
    public function __construct(
        private ?UserRepository $users = null,
        private ?UserAdminService $service = null
    ) {
        $this->users ??= new UserRepository();
        $this->service ??= new UserAdminService($this->users);
    }

    /** GET /admin/usuarios */
    public function index(): void
    {
        $this->guard();

        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'rol' => trim((string) ($_GET['rol'] ?? '')),
            'activo' => (string) ($_GET['activo'] ?? ''),
        ];

        $this->render('admin/usuarios/index', [
            'pageTitle' => 'Usuarios',
            'items' => $this->users->listAdmin($filters),
            'filters' => $filters,
            'roles' => $this->roleLabels(),
        ], 'admin');
    }

    /** GET /admin/usuarios/{id} */
    public function show(string $id): void
    {
        $this->guard();
        $user = $this->findOrRedirect((int) $id);

        $this->render('admin/usuarios/show', [
            'pageTitle' => 'Usuario: ' . $user['nombre'],
            'user' => $user,
            'reportCount' => $this->users->countReportsByUser((int) $user['id']),
            'badgeCount' => $this->users->countBadgesByUser((int) $user['id']),
        ], 'admin');
    }

    /** GET /admin/usuarios/{id}/editar */
    public function edit(string $id): void
    {
        $this->guard();
        $user = $this->findOrRedirect((int) $id);
        clear_old();

        $this->render('admin/usuarios/form', [
            'pageTitle' => 'Editar usuario',
            'user' => $user,
            'roles' => $this->roleLabels(),
            'errors' => [],
            'formAction' => url('/admin/usuarios/' . $user['id']),
        ], 'admin');
    }

    /** POST /admin/usuarios/{id} */
    public function update(string $id): void
    {
        $this->guard();
        $id = (int) $id;
        require_csrf('/admin/usuarios/' . $id . '/editar');
        $user = $this->findOrRedirect($id);

        $input = [
            'rol' => trim((string) ($_POST['rol'] ?? '')),
            'activo' => (string) ($_POST['activo'] ?? '0'),
        ];

        $result = $this->service->updateRoleAndActive($id, $input);

        if (!$result['ok']) {
            flash_old($input);
            $this->render('admin/usuarios/form', [
                'pageTitle' => 'Editar usuario',
                'user' => $user,
                'roles' => $this->roleLabels(),
                'errors' => $result['errors'] ?? [],
                'formAction' => url('/admin/usuarios/' . $id),
            ], 'admin');
            clear_old();
            return;
        }

        flash('success', 'Rol y estado del usuario actualizados.');
        $this->redirect('/admin/usuarios/' . $id);
    }

    private function guard(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN);
        deny_unless(
            can_manage_users(),
            'Solo el administrador puede gestionar usuarios.',
            '/admin'
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function findOrRedirect(int $id): array
    {
        $user = $this->users->findById($id);
        if (!$user) {
            flash('error', 'Usuario no encontrado.');
            $this->redirect('/admin/usuarios');
        }

        return $user;
    }

    /**
     * @return array<string,string>
     */
    private function roleLabels(): array
    {
        return [
            ROLE_ADMIN => 'Administrador',
            ROLE_DOCENTE => 'Docente',
            ROLE_ESTUDIANTE => 'Estudiante',
        ];
    }
}
