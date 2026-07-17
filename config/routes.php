<?php
/**
 * ============================================================================
 * routes.php — Mapa de rutas de la aplicación
 * ============================================================================
 * Aquí se declararán TODAS las URLs de Secretos Marinos.
 *
 * Paso 1: inicio
 * Paso 2: autenticación + panel privado
 * 
 * Formato:
 *   $router->get('/ruta', [ClaseController::class, 'metodo']);
 *   $router->post('/ruta', [ClaseController::class, 'metodo']);
 * ============================================================================
 */

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\PanelController;
use App\Core\Router;

/** @var Router $router  (inyectado desde public/index.php) */

// Público
$router->get('/', [HomeController::class, 'index']);

// Autenticación (Paso 2)
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/registro', [AuthController::class, 'showRegister']);
$router->post('/registro', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

// Área privada
$router->get('/panel', [PanelController::class, 'index']);
