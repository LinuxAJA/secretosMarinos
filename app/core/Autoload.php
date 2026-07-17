<?php
/**
 * ============================================================================
 * Autoload.php — Carga automática de clases (PSR-4 simplificado)
 * ============================================================================
 * Cuando PHP encuentra "new App\Core\Router()", llama a este autoload.
 * Convierte el namespace en ruta de archivo:
 *   App\Core\Router     → app/core/Router.php
 *   App\Controllers\X   → app/controllers/X.php
 *
 * Así no necesitamos require_once manual de cada clase.
 * ============================================================================
 */

spl_autoload_register(function (string $class): void {
    // Solo cargamos clases de nuestro namespace raíz "App\"
    $prefix = 'App\\';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return; // No es nuestra clase; otro autoload podría manejarla
    }

    // Quita "App\" y deja "Core\Router"
    $relative = substr($class, strlen($prefix));

    // Convierte namespace a ruta: Core\Router → core/Router.php
    // Nota: en Windows las carpetas son case-insensitive, pero en Linux sí importan.
    // Por eso guardamos carpetas en minúsculas (core, controllers, models...).
    $parts = explode('\\', $relative);
    $className = array_pop($parts); // último segmento = nombre del archivo
    $dirs = array_map('strtolower', $parts); // carpetas en minúsculas

    $path = APP_PATH;
    if (!empty($dirs)) {
        $path .= '/' . implode('/', $dirs);
    }
    $path .= '/' . $className . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});
