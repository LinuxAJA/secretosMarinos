<?php
/**
 * ============================================================================
 * public/index.php — Front Controller (punto de entrada único)
 * ============================================================================
 * TODAS las peticiones HTTP pasan por este archivo (gracias a .htaccess).
 *
 * Orden de arranque:
 *   1. Definir bandera de seguridad MISTERIOS_DEL_MAR
 *   2. Cargar constantes y configuración
 *   3. Activar autoload de clases
 *   4. Cargar helpers
 *   5. Configurar PHP (timezone, errores, sesión)
 *   6. Crear Router, cargar routes.php y despachar la URL
 * ============================================================================
 */

// Bandera: permite que config/*.php sepan que se incluyen legalmente
define('MISTERIOS_DEL_MAR', true);

// ---------------------------------------------------------------------------
// 1) Constantes y configuración
// ---------------------------------------------------------------------------
require_once dirname(__DIR__) . '/config/constants.php';
$appConfig = require CONFIG_PATH . '/app.php';

// ---------------------------------------------------------------------------
// 2) Autoload + helpers
// ---------------------------------------------------------------------------
require_once APP_PATH . '/core/Autoload.php';
require_once APP_PATH . '/helpers/helpers.php';

// ---------------------------------------------------------------------------
// 3) Entorno PHP
// ---------------------------------------------------------------------------
date_default_timezone_set(APP_TIMEZONE);
mb_internal_encoding(APP_CHARSET);

// En desarrollo mostramos errores; en producción los ocultamos
if (!empty($appConfig['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// ---------------------------------------------------------------------------
// 4) Sesión segura
// ---------------------------------------------------------------------------
$session = $appConfig['session'];

if (session_status() === PHP_SESSION_NONE) {
    session_name($session['name']);
    session_set_cookie_params([
        'lifetime' => $session['lifetime'],
        'path'     => '/',
        'httponly' => $session['cookie_httponly'],
        'secure'   => $session['cookie_secure'],
        'samesite' => $session['cookie_samesite'],
    ]);
    session_start();
}

// ---------------------------------------------------------------------------
// 5) Resolver URI limpia
// ---------------------------------------------------------------------------
// REQUEST_URI puede venir como: /misteriosDelMar/public/especies?x=1
// Queremos solo el path relativo al front controller: /especies
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Quita el prefijo APP_URL (/misteriosDelMar/public) para dejar la ruta interna
$base = rtrim(APP_URL, '/');
if ($base !== '' && str_starts_with($requestUri, $base)) {
    $requestUri = substr($requestUri, strlen($base));
}
if ($requestUri === '' || $requestUri === false) {
    $requestUri = '/';
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ---------------------------------------------------------------------------
// 6) Router
// ---------------------------------------------------------------------------
use App\Core\Router;

$router = new Router();
require CONFIG_PATH . '/routes.php';
$router->dispatch($requestUri, $method);
