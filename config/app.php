<?php
/**
 * ============================================================================
 * app.php — Configuración general de la aplicación
 * ============================================================================
 * Aquí van ajustes de comportamiento (debug, sesiones, límites de upload, etc.)
 * que pueden cambiar según el entorno sin tocar la lógica de negocio.
 * ============================================================================
 */

if (!defined('SECRETOS_MARINOS')) {
    die('Acceso no permitido.');
}

return [
    // Nombre visibles en títulos y correos
    'name' => APP_NAME,
    'version' => APP_VERSION,

    // true = muestra errores detallados (solo desarrollo local)
    'debug' => true,

    // URL base usada por el helper url()
    'url' => APP_URL,

    // Configuración de sesión PHP
    'session' => [
        'name' => SESSION_NAME,
        // cookie_httponly evita que JS lea la cookie de sesión (mitiga XSS)
        'cookie_httponly' => true,
        // cookie_secure = true solo si usas HTTPS
        'cookie_secure' => false,
        // SameSite reduce riesgo CSRF a nivel de cookie
        'cookie_samesite' => 'Lax',
        // Tiempo de vida de la sesión en segundos (2 horas)
        'lifetime' => 7200,
    ],

    // Límites de archivos subidos (bytes). 5 MB = 5 * 1024 * 1024
    'uploads' => [
        'max_size' => 5 * 1024 * 1024,
        'allowed_images' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'allowed_docs' => ['application/pdf'],
    ],

    // Paginación por defecto en listados
    'pagination' => [
        'per_page' => 10,
    ],
];
