<?php
/**
 * ============================================================================
 * routes.php — Mapa de rutas de la aplicación
 * ============================================================================
 * Aquí se declararán TODAS las URLs de Secretos Marinos.
 *
 * Formato:
 * $router->get('/ruta', [ClaseController::class, 'metodo']);
 * $router->post('/ruta', [ClaseController::class, 'metodo']);
 * 
 * Paso 1: inicio
 * Paso 2: autenticación + panel
 * Paso 3: educación + noticias (público) y CRUD admin
 * ============================================================================
 */

use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\ContentController as AdminContentController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\NewsController as AdminNewsController;
use App\Controllers\AuthController;
use App\Controllers\EducationController;
use App\Controllers\HomeController;
use App\Controllers\NewsController;
use App\Controllers\PanelController;
use App\Core\Router;

/** @var Router $router */

// Público
$router->get('/', [HomeController::class, 'index']);

// Autenticación
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/registro', [AuthController::class, 'showRegister']);
$router->post('/registro', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

// Panel usuario
$router->get('/panel', [PanelController::class, 'index']);

// Educación (público) — Paso 3
$router->get('/educacion', [EducationController::class, 'index']);
$router->get('/educacion/{slug}', [EducationController::class, 'show']);

// Noticias (público) — Paso 3
$router->get('/noticias', [NewsController::class, 'index']);
$router->get('/noticias/{slug}', [NewsController::class, 'show']);

// Admin dashboard
$router->get('/admin', [AdminDashboardController::class, 'index']);

// Admin contenidos (rutas estáticas antes de {id})
$router->get('/admin/contenidos', [AdminContentController::class, 'index']);
$router->get('/admin/contenidos/crear', [AdminContentController::class, 'create']);
$router->post('/admin/contenidos', [AdminContentController::class, 'store']);
$router->get('/admin/contenidos/{id}/editar', [AdminContentController::class, 'edit']);
$router->post('/admin/contenidos/{id}', [AdminContentController::class, 'update']);
$router->post('/admin/contenidos/{id}/eliminar', [AdminContentController::class, 'destroy']);

// Admin categorías
$router->get('/admin/categorias', [AdminCategoryController::class, 'index']);
$router->get('/admin/categorias/crear', [AdminCategoryController::class, 'create']);
$router->post('/admin/categorias', [AdminCategoryController::class, 'store']);
$router->get('/admin/categorias/{id}/editar', [AdminCategoryController::class, 'edit']);
$router->post('/admin/categorias/{id}', [AdminCategoryController::class, 'update']);
$router->post('/admin/categorias/{id}/eliminar', [AdminCategoryController::class, 'destroy']);

// Admin noticias
$router->get('/admin/noticias', [AdminNewsController::class, 'index']);
$router->get('/admin/noticias/crear', [AdminNewsController::class, 'create']);
$router->post('/admin/noticias', [AdminNewsController::class, 'store']);
$router->get('/admin/noticias/{id}/editar', [AdminNewsController::class, 'edit']);
$router->post('/admin/noticias/{id}', [AdminNewsController::class, 'update']);
$router->post('/admin/noticias/{id}/eliminar', [AdminNewsController::class, 'destroy']);
