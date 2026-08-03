<?php
/**
 * ============================================================================
 * constants.php — Constantes globales del proyecto Misterios Del Mar
 * ============================================================================
 * Aquí definimos valores fijos que NO cambian en tiempo de ejecución
 * (nombre de la app, rutas base, roles, estados, etc.).
 * Usar constants evita "números/cadenas mágicas" repartidas por el código.
 * ============================================================================
 */

// Impide acceso directo al archivo (debe incluirse desde index.php u otro bootstrap)
if (!defined('MISTERIOS_DEL_MAR')) {
    die('Acceso no permitido.');
}

// ---------------------------------------------------------------------------
// Identidad de la aplicación
 // ---------------------------------------------------------------------------
define('APP_NAME', 'Misterios Del Mar');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'local'); // local | production

// ---------------------------------------------------------------------------
// Zona horaria y charset
 // ---------------------------------------------------------------------------
define('APP_TIMEZONE', 'America/Bogota');
define('APP_CHARSET', 'UTF-8');

// ---------------------------------------------------------------------------
// Rutas del sistema de archivos (absolutas en disco)
 // BASE_PATH = raíz del proyecto (carpeta misteriosDelMar)
 // ---------------------------------------------------------------------------
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('VIEWS_PATH', BASE_PATH . '/views');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');

/**
 * URL base pública.
 * En XAMPP, si abres: http://localhost/misteriosDelMar/public/
 * entonces APP_URL debe coincidir con esa carpeta pública.
 * Si más adelante mueves el DocumentRoot a /public, cámbiala a '/'.
 */
define('APP_URL', '/misteriosDelMar/public');

// Rutas URL de assets y uploads (para <link>, <script>, <img>)
define('ASSETS_URL', '/misteriosDelMar/assets');
define('UPLOADS_URL', '/misteriosDelMar/uploads');

// ---------------------------------------------------------------------------
// Roles del sistema (coinciden con la tabla `roles`)
 // ---------------------------------------------------------------------------
define('ROLE_ADMIN', 'admin');
define('ROLE_DOCENTE', 'docente');
define('ROLE_ESTUDIANTE', 'estudiante');

// ---------------------------------------------------------------------------
// Estados de reportes ambientales
 // ---------------------------------------------------------------------------
define('REPORTE_PENDIENTE', 'pendiente');
define('REPORTE_EN_REVISION', 'en_revision');
define('REPORTE_RESUELTO', 'resuelto');

// ---------------------------------------------------------------------------
// Seguridad / sesión
 // ---------------------------------------------------------------------------
define('SESSION_NAME', 'misterios_del_mar_session');
define('CSRF_TOKEN_KEY', '_csrf_token');
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCK_MINUTES', 15);
