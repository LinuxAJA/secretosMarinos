<?php
/**
 * ============================================================================
 * Admin/ReportController.php — Cola de revisión de reportes
 * ============================================================================
 * Admin y docente pueden listar y cambiar estado/notas.
 * Solo el admin elimina reportes ajenos (moderación).
 * ============================================================================
 */

namespace App\Controllers\Admin;

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
     * GET /admin/reportes — Cola con filtros por estado y tipo.
     */
    public function index(): void
    {
        $this->guard();
        $estado = trim($_GET['estado'] ?? '');
        $tipo = trim($_GET['tipo'] ?? '');

        $state = isset(ReportService::STATES[$estado]) ? $estado : null;
        $type = isset(ReportService::TYPES[$tipo]) ? $tipo : null;

        $this->render('admin/reportes/index', [
            'pageTitle' => 'Reportes ambientales',
            'items' => $this->reports->listAll($state, $type),
            'types' => ReportService::TYPES,
            'states' => ReportService::STATES,
            'filters' => ['estado' => $estado, 'tipo' => $tipo],
            'pendingCount' => $this->reports->countByEstado('pendiente'),
        ], 'admin');
    }

    /**
     * GET /admin/reportes/{id}/editar — Formulario de revisión.
     */
    public function edit(string $id): void
    {
        $this->guard();
        $item = $this->findOrRedirect((int) $id);
        clear_old();

        $this->render('admin/reportes/form', [
            'pageTitle' => 'Revisar reporte',
            'item' => $item,
            'errors' => [],
            'action' => url('/admin/reportes/' . $item['id']),
            'types' => ReportService::TYPES,
            'states' => ReportService::STATES,
        ], 'admin');
    }

    /**
     * POST /admin/reportes/{id} — Guarda revisión (estado + notas).
     */
    public function update(string $id): void
    {
        $this->guard();
        $id = (int) $id;
        require_csrf('/admin/reportes/' . $id . '/editar');
        $item = $this->findOrRedirect($id);

        $input = [
            'estado' => trim($_POST['estado'] ?? ''),
            'notas_revision' => trim($_POST['notas_revision'] ?? ''),
        ];

        $result = $this->service->review($id, $input, (int) current_user()['id']);

        if (!$result['ok']) {
            flash_old($input);
            $this->render('admin/reportes/form', [
                'pageTitle' => 'Revisar reporte',
                'item' => $item,
                'errors' => $result['errors'] ?? [],
                'action' => url('/admin/reportes/' . $id),
                'types' => ReportService::TYPES,
                'states' => ReportService::STATES,
            ], 'admin');
            clear_old();
            return;
        }

        $message = 'Revisión guardada.';
        if (!empty($result['newBadges'])) {
            $names = array_map(
                static fn(array $b): string => (string) ($b['nombre'] ?? 'Insignia'),
                $result['newBadges']
            );
            $message .= ' El autor desbloqueó: ' . implode(', ', $names) . '.';
        }
        flash('success', $message);
        $this->redirect('/admin/reportes');
    }

    /**
     * POST /admin/reportes/{id}/eliminar — Moderación (solo admin).
     */
    public function destroy(string $id): void
    {
        $this->guard();
        require_csrf('/admin/reportes');
        deny_unless(
            can_delete_any_report(),
            'Solo el administrador puede eliminar reportes ajenos.',
            '/admin/reportes'
        );

        $this->findOrRedirect((int) $id);

        if ($this->service->delete((int) $id)) {
            flash('success', 'Reporte eliminado.');
        } else {
            flash('error', 'No se pudo eliminar el reporte.');
        }
        $this->redirect('/admin/reportes');
    }

    private function guard(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
    }

    /** @return array<string,mixed> */
    private function findOrRedirect(int $id): array
    {
        $item = $this->reports->findById($id);
        if (!$item) {
            flash('error', 'Reporte no encontrado.');
            $this->redirect('/admin/reportes');
        }
        return $item;
    }
}
