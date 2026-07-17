<?php
/**
 * ============================================================================
 * routes.php — Mapa de rutas de la aplicación
 * ============================================================================
 * Aquí se declararán TODAS las URLs de Secretos Marinos.
 * En el Paso 1 solo existe la ruta de inicio.
 * En pasos siguientes agregaremos /login, /especies, /admin, etc.
 *
 * Formato:
 *   $router->get('/ruta', [ClaseController::class, 'metodo']);
 *   $router->post('/ruta', [ClaseController::class, 'metodo']);
 * ============================================================================
 */

use App\Controllers\HomeController;
use App\Core\Router;

/** @var Router $router  (inyectado desde public/index.php) */

// Página de inicio
$router->get('/', [HomeController::class, 'index']);
