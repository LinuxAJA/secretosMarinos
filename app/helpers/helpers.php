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
 * Construye la URL pública de un archivo subido.
 */
function upload_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    return rtrim(UPLOADS_URL, '/') . '/' . ltrim($path, '/');
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

/**
 * ¿El usuario tiene alguno de los roles listados?
 */
function has_any_role(string ...$roles): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    return in_array($user['rol'] ?? '', $roles, true);
}

/**
 * ---------------------------------------------------------------------------
 * Políticas de autorización (RBAC Paso 3)
 * ---------------------------------------------------------------------------
 * Admin  → control global
 * Docente → gestiona solo lo que él creó (autor_id)
 * Estudiante → sin acceso admin
 */

function is_admin(): bool
{
    return has_role(ROLE_ADMIN);
}

function is_docente(): bool
{
    return has_role(ROLE_DOCENTE);
}

/**
 * Solo el administrador gestiona la taxonomía (categorías).
 */
function can_manage_categories(): bool
{
    return is_admin();
}

/**
 * ¿Puede crear/editar/eliminar un contenido o noticia?
 * - Admin: siempre
 * - Docente: solo si es el autor
 * - Sin ítem (crear nuevo): admin y docente sí
 *
 * @param array<string,mixed>|null $item Fila con clave autor_id (null = alta nueva)
 */
function can_manage_authored_item(?array $item = null): bool
{
    if (!is_logged_in()) {
        return false;
    }

    if (is_admin()) {
        return true;
    }

    if (!is_docente()) {
        return false;
    }

    // Alta nueva: el docente puede crear (quedará con su autor_id)
    if ($item === null) {
        return true;
    }

    $autorId = isset($item['autor_id']) ? (int) $item['autor_id'] : 0;
    $userId  = (int) (current_user()['id'] ?? 0);

    return $autorId > 0 && $autorId === $userId;
}

function can_manage_content(?array $item = null): bool
{
    return can_manage_authored_item($item);
}

function can_manage_news(?array $item = null): bool
{
    return can_manage_authored_item($item);
}

/**
 * Los ecosistemas son taxonomía global: solo los administra el admin.
 */
function can_manage_ecosystems(): bool
{
    return is_admin();
}

/**
 * Las especies siguen autoría: admin global, docente solo las propias.
 */
function can_manage_species(?array $item = null): bool
{
    return can_manage_authored_item($item);
}

/**
 * ---------------------------------------------------------------------------
 * Políticas Paso 5 — Campañas y reportes
 * ---------------------------------------------------------------------------
 */

/**
 * Campañas: admin global; docente solo si es responsable_id.
 *
 * @param array<string,mixed>|null $item Fila con responsable_id (null = alta nueva)
 */
function can_manage_campaigns(?array $item = null): bool
{
    if (!is_logged_in()) {
        return false;
    }

    if (is_admin()) {
        return true;
    }

    if (!is_docente()) {
        return false;
    }

    // Alta nueva: el docente puede crear (quedará como responsable)
    if ($item === null) {
        return true;
    }

    $responsableId = isset($item['responsable_id']) ? (int) $item['responsable_id'] : 0;
    $userId = (int) (current_user()['id'] ?? 0);

    return $responsableId > 0 && $responsableId === $userId;
}

/**
 * ¿Puede crear un reporte ambiental? Cualquier usuario autenticado.
 */
function can_create_report(): bool
{
    return is_logged_in();
}

/**
 * ¿Puede ver un reporte concreto?
 * - Staff (admin/docente): cualquiera
 * - Autor: el suyo
 * - Público anónimo: solo si está resuelto (se valida en controller)
 *
 * @param array<string,mixed> $report
 */
function can_view_report(array $report): bool
{
    if (can_review_reports()) {
        return true;
    }

    if (!is_logged_in()) {
        return ($report['estado'] ?? '') === 'resuelto';
    }

    $ownerId = isset($report['usuario_id']) ? (int) $report['usuario_id'] : 0;
    $userId = (int) (current_user()['id'] ?? 0);

    if ($ownerId > 0 && $ownerId === $userId) {
        return true;
    }

    return ($report['estado'] ?? '') === 'resuelto';
}

/**
 * ¿Puede editar/eliminar su propio reporte? Solo el autor y solo si está pendiente.
 *
 * @param array<string,mixed> $report
 */
function can_edit_own_report(array $report): bool
{
    if (!is_logged_in()) {
        return false;
    }

    if (($report['estado'] ?? '') !== 'pendiente') {
        return false;
    }

    $ownerId = isset($report['usuario_id']) ? (int) $report['usuario_id'] : 0;
    $userId = (int) (current_user()['id'] ?? 0);

    return $ownerId > 0 && $ownerId === $userId;
}

/**
 * Staff que revisa reportes (cambia estado y deja notas): admin y docente.
 */
function can_review_reports(): bool
{
    return is_admin() || is_docente();
}

/**
 * Eliminar cualquier reporte (moderación): solo admin.
 * El autor usa can_edit_own_report() mientras esté pendiente.
 */
function can_delete_any_report(): bool
{
    return is_admin();
}

/**
 * Bloquea la acción si no hay permiso: flash + redirect.
 */
function deny_unless(bool $allowed, string $message, string $redirectTo): void
{
    if ($allowed) {
        return;
    }
    flash('error', $message);
    redirect_to($redirectTo);
}

/**
 * Redirección global (usable desde middlewares y helpers, no solo controllers).
 */
function redirect_to(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Exige token CSRF válido en peticiones POST.
 * Si falla, muestra flash y redirige (o aborta).
 */
function require_csrf(?string $redirectTo = null): void
{
    $token = $_POST['_csrf'] ?? null;
    if (!verify_csrf(is_string($token) ? $token : null)) {
        flash('error', 'Token de seguridad inválido. Vuelve a enviar el formulario.');
        redirect_to($redirectTo ?? '/');
    }
}

/**
 * Recupera valores "old" del formulario tras un error de validación.
 * Se guardan en sesión con flash_old() / get_old().
 */
function flash_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function get_old(string $key, string $default = ''): string
{
    $value = $_SESSION['_old'][$key] ?? $default;
    return is_string($value) ? $value : $default;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

/**
 * Convierte un título en slug URL-amigable.
 * Ejemplo: "¿Qué es el océano?" → "que-es-el-oceano"
 */
function slugify(string $text): string
{
    $text = trim(mb_strtolower($text, 'UTF-8'));

    // Transliteración básica español → ASCII
    $map = [
        'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
        'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
        'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
        'ñ' => 'n', 'ç' => 'c',
    ];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
    $text = trim($text, '-');

    return $text !== '' ? $text : 'item';
}

/**
 * Recorta texto para listados (resúmenes).
 */
function excerpt(?string $text, int $limit = 160): string
{
    $text = trim(strip_tags($text ?? ''));
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
}

/**
 * Formatea fecha MySQL a formato legible es-CO.
 */
function format_date(?string $datetime, string $format = 'd/m/Y'): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    return $ts ? date($format, $ts) : '';
}

/**
 * Obtiene old input tipado (útil para selects/checkboxes en edición).
 */
function old(string $key, mixed $default = ''): mixed
{
    if (array_key_exists($key, $_SESSION['_old'] ?? [])) {
        return $_SESSION['_old'][$key];
    }
    return $default;
}
