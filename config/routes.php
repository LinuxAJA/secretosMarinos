<?php
/**
 * ============================================================================
 * routes.php — Mapa de rutas de la aplicación
 * ============================================================================
 * Aquí se declararán TODAS las URLs de Misterios Del Mar.
 *
 * Formato:
 * $router->get('/ruta', [ClaseController::class, 'metodo']);
 * $router->post('/ruta', [ClaseController::class, 'metodo']);
 * 
 * Paso 1: inicio
 * Paso 2: autenticación + panel
 * Paso 3: educación + noticias (público) y CRUD admin
 * Complemento auth: edición de perfil en /panel
 * Paso 4: especies marinas + ecosistemas
 * Paso 5: campañas ambientales + reportes ciudadanos
 * Paso 6: gamificación (puntos e insignias)
 * Paso 7: admin ampliado (usuarios) + estadísticas básicas
 * ============================================================================
 */

use App\Controllers\Admin\BadgeController as AdminBadgeController;
use App\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\ContentController as AdminContentController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\EcosystemController as AdminEcosystemController;
use App\Controllers\Admin\NewsController as AdminNewsController;
use App\Controllers\Admin\PointsController as AdminPointsController;
use App\Controllers\Admin\ReportController as AdminReportController;
use App\Controllers\Admin\SpeciesController as AdminSpeciesController;
use App\Controllers\Admin\StatsController as AdminStatsController;
use App\Controllers\Admin\UserController as AdminUserController;

use App\Controllers\AuthController;
use App\Controllers\BadgeController;
use App\Controllers\CampaignController;
use App\Controllers\EducationController;
use App\Controllers\EcosystemController;
use App\Controllers\HomeController;
use App\Controllers\NewsController;
use App\Controllers\PanelController;
use App\Controllers\RankingController;
use App\Controllers\ReportController;
use App\Controllers\SpeciesController;

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

// Panel usuario + perfil (complemento auth)
$router->get('/panel', [PanelController::class, 'index']);
$router->post('/panel/perfil', [PanelController::class, 'updateProfile']);
$router->post('/panel/password', [PanelController::class, 'updatePassword']);
$router->post('/panel/eliminar', [PanelController::class, 'destroyAccount']);

// Educación (público) — Paso 3
$router->get('/educacion', [EducationController::class, 'index']);
$router->get('/educacion/{slug}', [EducationController::class, 'show']);

// Noticias (público) — Paso 3
$router->get('/noticias', [NewsController::class, 'index']);
$router->get('/noticias/{slug}', [NewsController::class, 'show']);

// Catálogo científico (público) — Paso 4
$router->get('/ecosistemas', [EcosystemController::class, 'index']);
$router->get('/ecosistemas/{slug}', [EcosystemController::class, 'show']);
$router->get('/especies', [SpeciesController::class, 'index']);
$router->get('/especies/{slug}', [SpeciesController::class, 'show']);

// Campañas (público) — Paso 5
$router->get('/campanias', [CampaignController::class, 'index']);
$router->get('/campanias/{slug}', [CampaignController::class, 'show']);

// Reportes ambientales (público + ciudadano autenticado) — Paso 5
// Rutas estáticas antes de {id}
$router->get('/reportes', [ReportController::class, 'index']);
$router->get('/reportes/crear', [ReportController::class, 'create']);
$router->post('/reportes', [ReportController::class, 'store']);
$router->get('/reportes/{id}/editar', [ReportController::class, 'edit']);
$router->post('/reportes/{id}', [ReportController::class, 'update']);
$router->post('/reportes/{id}/eliminar', [ReportController::class, 'destroy']);
$router->get('/reportes/{id}', [ReportController::class, 'show']);

// Gamificación (público) — Paso 6
$router->get('/insignias', [BadgeController::class, 'index']);
$router->get('/ranking', [RankingController::class, 'index']);

// Admin dashboard + estadísticas (Paso 7)
$router->get('/admin', [AdminDashboardController::class, 'index']);
$router->get('/admin/estadisticas', [AdminStatsController::class, 'index']);

// Admin usuarios — solo admin (Paso 7); rutas estáticas antes de {id}
$router->get('/admin/usuarios', [AdminUserController::class, 'index']);
$router->get('/admin/usuarios/{id}/editar', [AdminUserController::class, 'edit']);
$router->post('/admin/usuarios/{id}', [AdminUserController::class, 'update']);
$router->get('/admin/usuarios/{id}', [AdminUserController::class, 'show']);

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

// Admin ecosistemas — CRUD solo admin; listado también docente
$router->get('/admin/ecosistemas', [AdminEcosystemController::class, 'index']);
$router->get('/admin/ecosistemas/crear', [AdminEcosystemController::class, 'create']);
$router->post('/admin/ecosistemas', [AdminEcosystemController::class, 'store']);
$router->get('/admin/ecosistemas/{id}/editar', [AdminEcosystemController::class, 'edit']);
$router->post('/admin/ecosistemas/{id}', [AdminEcosystemController::class, 'update']);
$router->post('/admin/ecosistemas/{id}/eliminar', [AdminEcosystemController::class, 'destroy']);

// Admin especies — admin global; docente solo autoría propia
$router->get('/admin/especies', [AdminSpeciesController::class, 'index']);
$router->get('/admin/especies/crear', [AdminSpeciesController::class, 'create']);
$router->post('/admin/especies', [AdminSpeciesController::class, 'store']);
$router->get('/admin/especies/{id}/editar', [AdminSpeciesController::class, 'edit']);
$router->post('/admin/especies/{id}', [AdminSpeciesController::class, 'update']);
$router->post('/admin/especies/{id}/eliminar', [AdminSpeciesController::class, 'destroy']);

// Admin campañas — admin global; docente solo responsabilidad propia
$router->get('/admin/campanias', [AdminCampaignController::class, 'index']);
$router->get('/admin/campanias/crear', [AdminCampaignController::class, 'create']);
$router->post('/admin/campanias', [AdminCampaignController::class, 'store']);
$router->get('/admin/campanias/{id}/editar', [AdminCampaignController::class, 'edit']);
$router->post('/admin/campanias/{id}', [AdminCampaignController::class, 'update']);
$router->post('/admin/campanias/{id}/eliminar', [AdminCampaignController::class, 'destroy']);

// Admin reportes — cola de revisión (admin/docente); eliminar solo admin
$router->get('/admin/reportes', [AdminReportController::class, 'index']);
$router->get('/admin/reportes/{id}/editar', [AdminReportController::class, 'edit']);
$router->post('/admin/reportes/{id}', [AdminReportController::class, 'update']);
$router->post('/admin/reportes/{id}/eliminar', [AdminReportController::class, 'destroy']);

// Admin insignias — CRUD solo admin; listado también docente
$router->get('/admin/insignias', [AdminBadgeController::class, 'index']);
$router->get('/admin/insignias/crear', [AdminBadgeController::class, 'create']);
$router->post('/admin/insignias', [AdminBadgeController::class, 'store']);
$router->get('/admin/insignias/{id}/editar', [AdminBadgeController::class, 'edit']);
$router->post('/admin/insignias/{id}', [AdminBadgeController::class, 'update']);
$router->post('/admin/insignias/{id}/eliminar', [AdminBadgeController::class, 'destroy']);

// Admin ajuste de puntos — solo admin
$router->get('/admin/puntos', [AdminPointsController::class, 'create']);
$router->post('/admin/puntos', [AdminPointsController::class, 'store']);
