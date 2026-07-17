<?php
/**
 * ============================================================================
 * helpers.php — Funciones auxiliares globales
 * ============================================================================
 * Estas funciones se cargan siempre al iniciar la app.
 * Centralizan tareas repetidas: URLs, escape HTML, flash messages, CSRF.
 * ============================================================================
 */

/**
 * Construye una URL absoluta de la aplicación.
 * Ejemplo: url('/login') → /secretosMarinos/public/login
 */
function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    if ($path === '/') {
        return rtrim(APP_URL, '/');
    }
    return rtrim(APP_URL, '/') . $path;
}

/**
 * URL de un asset estático (CSS, JS, imágenes).
 * Ejemplo: asset('css/main.css') → /secretosMarinos/assets/css/main.css
 */
function asset(string $path): string
{
    return rtrim(ASSETS_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Escapa texto para imprimirlo de forma segura en HTML (previene XSS).
 * Siempre usa e() al mostrar datos del usuario o de la BD en vistas.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Guarda un mensaje flash en sesión (se muestra una sola vez).
 * Tipos sugeridos: success | error | warning | info
 */
function flash(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Obtiene y elimina el mensaje flash (si existe).
 */
function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Genera (o reutiliza) un token CSRF y lo guarda en sesión.
 * Se coloca en formularios como <input type="hidden" name="_csrf" value="...">
 */
function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        // random_bytes genera bytes criptográficamente seguros
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

/**
 * Campo HTML hidden con el token CSRF listo para pegar en formularios.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Verifica que el token CSRF del formulario coincida con el de la sesión.
 */
function verify_csrf(?string $token): bool
{
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_KEY])) {
        return false;
    }
    // hash_equals evita ataques de timing
    return hash_equals($_SESSION[CSRF_TOKEN_KEY], $token);
}

/**
 * ¿Hay un usuario autenticado en sesión?
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user']['id']);
}

/**
 * Devuelve el usuario de sesión o null.
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * ¿El usuario actual tiene el rol indicado?
 */
function has_role(string $role): bool
{
    $user = current_user();
    return $user && (($user['rol'] ?? '') === $role);
}
