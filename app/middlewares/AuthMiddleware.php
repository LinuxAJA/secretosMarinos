<?php
/**
 * ============================================================================
 * AuthMiddleware.php — Control de acceso por autenticación y roles
 * ============================================================================
 * Un middleware intercepta la petición ANTES de la lógica del controlador.
 * Aquí lo usamos como métodos estáticos llamados al inicio de cada acción
 * protegida.
 *
 * Uso típico en un controlador:
 *   AuthMiddleware::requireAuth();
 *   AuthMiddleware::requireRole(ROLE_ADMIN, ROLE_DOCENTE);
 *   AuthMiddleware::requireGuest(); // solo visitantes (login/registro)
 * ============================================================================
 */

namespace App\Middlewares;

class AuthMiddleware
{
    /**
     * Exige usuario autenticado. Si no hay sesión → redirige a login.
     */
    public static function requireAuth(): void
    {
        if (!is_logged_in()) {
            flash('warning', 'Debes iniciar sesión para continuar.');
            redirect_to('/login');
        }
    }

    /**
     * Exige que NO haya sesión (páginas de login/registro).
     * Si ya está logueado → lo manda al panel.
     */
    public static function requireGuest(): void
    {
        if (is_logged_in()) {
            redirect_to('/panel');
        }
    }

    /**
     * Exige que el usuario tenga uno de los roles indicados.
     * Ejemplo: requireRole(ROLE_ADMIN) o requireRole(ROLE_ADMIN, ROLE_DOCENTE)
     */
    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();

        $user = current_user();
        $rol  = $user['rol'] ?? '';

        if (!in_array($rol, $roles, true)) {
            flash('error', 'No tienes permisos para acceder a esta sección.');
            redirect_to('/panel');
        }
    }
}
