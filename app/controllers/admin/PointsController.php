<?php
/**
 * ============================================================================
 * Admin/PointsController.php — Ajuste manual de puntos (solo admin)
 * ============================================================================
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;
use App\Repositories\UserRepository;
use App\Services\GamificationService;

class PointsController extends Controller
{
    public function __construct(
        private ?UserRepository $users = null,
        private ?GamificationService $gamification = null
    ) {
        $this->users ??= new UserRepository();
        $this->gamification ??= new GamificationService();
    }

    /** GET /admin/puntos */
    public function create(): void
    {
        $this->guard();
        clear_old();
        $this->render('admin/puntos/form', [
            'pageTitle' => 'Ajustar puntos',
            'users' => $this->users->listActiveForSelect(),
            'errors' => [],
        ], 'admin');
    }

    /** POST /admin/puntos */
    public function store(): void
    {
        $this->guard();
        require_csrf('/admin/puntos');

        $input = [
            'usuario_id' => (int) ($_POST['usuario_id'] ?? 0),
            'puntos' => (int) ($_POST['puntos'] ?? 0),
            'motivo' => trim($_POST['motivo'] ?? ''),
        ];

        $result = $this->gamification->adjustPoints(
            $input['usuario_id'],
            $input['puntos'],
            $input['motivo']
        );

        if (!$result['ok']) {
            flash_old([
                'usuario_id' => (string) $input['usuario_id'],
                'puntos' => (string) $input['puntos'],
                'motivo' => $input['motivo'],
            ]);
            $this->render('admin/puntos/form', [
                'pageTitle' => 'Ajustar puntos',
                'users' => $this->users->listActiveForSelect(),
                'errors' => $result['errors'] ?? [],
            ], 'admin');
            clear_old();
            return;
        }

        $message = 'Puntos actualizados. Nuevo saldo: ' . (int) ($result['newTotal'] ?? 0) . '.';
        if (!empty($result['newBadges'])) {
            $names = array_map(
                static fn(array $b): string => (string) ($b['nombre'] ?? 'Insignia'),
                $result['newBadges']
            );
            $message .= ' Insignias nuevas: ' . implode(', ', $names) . '.';
        }
        flash('success', $message);
        $this->redirect('/admin/puntos');
    }

    private function guard(): void
    {
        AuthMiddleware::requireRole(ROLE_ADMIN);
        deny_unless(
            can_adjust_points(),
            'Solo el administrador puede ajustar puntos.',
            '/admin'
        );
    }
}
