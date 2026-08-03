<?php
/**
 * ============================================================================
 * ReportController.php — Participación ciudadana (reportes ambientales)
 * ============================================================================
 * Flujos públicos y de usuario autenticado:
 * - Listado de resueltos
 * - Crear / editar / eliminar propio (si pendiente)
 * - Ver ficha según ownership o estado resuelto
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\ReportRepository;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(
        private ?ReportRepository $reports = null,
        private ?ReportService $service = null
    ) {
        $this->reports ??= new ReportRepository();
        $this->service ??= new ReportService($this->reports);
    }

    /**
     * GET /reportes — Catálogo público de reportes resueltos.
     */
    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $tipo = trim($_GET['tipo'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;

        $query = $q !== '' ? $q : null;
        $type = isset(ReportService::TYPES[$tipo]) ? $tipo : null;

        $total = $this->reports->countResolved($query, $type);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $myReports = [];
        if (is_logged_in()) {
            $myReports = $this->reports->listByUser((int) current_user()['id']);
        }

        $this->render('pages/reportes/index', [
            'pageTitle' => 'Reportes ambientales',
            'items' => $this->reports->listResolved(
                $query,
                $type,
                $perPage,
                ($page - 1) * $perPage
            ),
            'myReports' => $myReports,
            'types' => ReportService::TYPES,
            'states' => ReportService::STATES,
            'filters' => ['q' => $q, 'tipo' => $tipo],
            'pagination' => compact('page', 'pages', 'total'),
        ]);
    }

    /**
     * GET /reportes/crear — Formulario de alta (requiere login).
     */
    public function create(): void
    {
        AuthMiddleware::requireAuth();
        deny_unless(can_create_report(), 'No puedes crear reportes.', '/reportes');
        clear_old();

        $this->render('pages/reportes/form', [
            'pageTitle' => 'Crear reporte ambiental',
            'item' => null,
            'errors' => [],
            'action' => url('/reportes'),
            'types' => ReportService::TYPES,
        ]);
    }

    /**
     * POST /reportes — Guarda un nuevo reporte pendiente.
     */
    public function store(): void
    {
        AuthMiddleware::requireAuth();
        require_csrf('/reportes/crear');
        deny_unless(can_create_report(), 'No puedes crear reportes.', '/reportes');

        $input = $this->citizenInput();
        $result = $this->service->create(
            $input,
            $_FILES['imagen'] ?? null,
            (int) current_user()['id']
        );

        if (!$result['ok']) {
            flash_old($input);
            $this->render('pages/reportes/form', [
                'pageTitle' => 'Crear reporte ambiental',
                'item' => null,
                'errors' => $result['errors'] ?? [],
                'action' => url('/reportes'),
                'types' => ReportService::TYPES,
            ]);
            clear_old();
            return;
        }

        $message = 'Reporte enviado. Quedó en estado pendiente de revisión.';
        if (!empty($result['pointsAwarded'])) {
            $message .= ' +' . \App\Services\GamificationService::POINTS_REPORT_CREATED . ' puntos ecológicos.';
        }
        if (!empty($result['newBadges'])) {
            $names = array_map(
                static fn(array $b): string => (string) ($b['nombre'] ?? 'Insignia'),
                $result['newBadges']
            );
            $message .= ' ¡Nueva insignia!: ' . implode(', ', $names) . '.';
        }
        flash('success', $message);
        $this->redirect('/reportes/' . $result['id']);
    }

    /**
     * GET /reportes/{id} — Ficha según permisos de visibilidad.
     *
     * @param string $id Identificador numérico del reporte
     */
    public function show(string $id): void
    {
        $item = $this->findOrRedirect((int) $id);
        deny_unless(
            can_view_report($item),
            'No tienes permiso para ver este reporte.',
            '/reportes'
        );

        $this->render('pages/reportes/show', [
            'pageTitle' => $item['titulo'],
            'item' => $item,
            'types' => ReportService::TYPES,
            'states' => ReportService::STATES,
            'canEdit' => can_edit_own_report($item),
        ]);
    }

    /**
     * GET /reportes/{id}/editar — Edición del autor (solo pendiente).
     */
    public function edit(string $id): void
    {
        AuthMiddleware::requireAuth();
        $item = $this->findOrRedirect((int) $id);
        deny_unless(
            can_edit_own_report($item),
            'Solo puedes editar tus reportes pendientes.',
            '/reportes/' . $id
        );
        clear_old();

        $this->render('pages/reportes/form', [
            'pageTitle' => 'Editar reporte',
            'item' => $item,
            'errors' => [],
            'action' => url('/reportes/' . $item['id']),
            'types' => ReportService::TYPES,
        ]);
    }

    /**
     * POST /reportes/{id} — Guarda cambios del autor.
     */
    public function update(string $id): void
    {
        AuthMiddleware::requireAuth();
        $id = (int) $id;
        require_csrf('/reportes/' . $id . '/editar');
        $item = $this->findOrRedirect($id);
        deny_unless(
            can_edit_own_report($item),
            'Solo puedes editar tus reportes pendientes.',
            '/reportes/' . $id
        );

        $input = $this->citizenInput();
        $result = $this->service->updateByOwner($id, $input, $_FILES['imagen'] ?? null);

        if (!$result['ok']) {
            flash_old($input);
            $this->render('pages/reportes/form', [
                'pageTitle' => 'Editar reporte',
                'item' => $item,
                'errors' => $result['errors'] ?? [],
                'action' => url('/reportes/' . $id),
                'types' => ReportService::TYPES,
            ]);
            clear_old();
            return;
        }

        flash('success', 'Reporte actualizado.');
        $this->redirect('/reportes/' . $id);
    }

    /**
     * POST /reportes/{id}/eliminar — Borrado del autor (pendiente) o denegado.
     */
    public function destroy(string $id): void
    {
        AuthMiddleware::requireAuth();
        require_csrf('/reportes');
        $item = $this->findOrRedirect((int) $id);

        $allowed = can_edit_own_report($item) || can_delete_any_report();
        deny_unless($allowed, 'No puedes eliminar este reporte.', '/reportes');

        if ($this->service->delete((int) $id)) {
            flash('success', 'Reporte eliminado.');
        } else {
            flash('error', 'No se pudo eliminar el reporte.');
        }

        // Admin vuelve a la cola; ciudadano al listado público
        $this->redirect(can_review_reports() ? '/admin/reportes' : '/reportes');
    }

    /**
     * @return array<string,mixed>
     */
    private function findOrRedirect(int $id): array
    {
        $item = $this->reports->findById($id);
        if (!$item) {
            flash('error', 'Reporte no encontrado.');
            $this->redirect('/reportes');
        }
        return $item;
    }

    /**
     * Extrae y limpia el input del formulario ciudadano.
     *
     * @return array<string,string>
     */
    private function citizenInput(): array
    {
        return [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'ubicacion' => trim($_POST['ubicacion'] ?? ''),
            'tipo' => trim($_POST['tipo'] ?? 'otro'),
            'eliminar_imagen' => !empty($_POST['eliminar_imagen']) ? '1' : '',
        ];
    }
}
