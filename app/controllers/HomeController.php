<?php
/**
 * ============================================================================
 * HomeController.php — Controlador de la página de inicio
 * ============================================================================
 * En MVC, el controlador:
 *   1. Recibe la petición (vía Router)
 *   2. (Más adelante) pide datos a modelos/repositorios
 *   3. Pasa esos datos a una vista
 *
 * En el Paso 1 solo mostramos la portada estática/demo.
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    /**
     * GET / — Página de inicio pública.
     */
    public function index(): void
    {
        // Datos que la vista podrá usar como variables ($titulo, $subtitulo...)
        $this->render('pages/home', [
            'pageTitle' => 'Inicio',
            'heroTitle' => 'Descubre, aprende y protege el océano',
            'heroText'  => 'Plataforma de alfabetización oceánica y acción ambiental para estudiantes, docentes y comunidad.',
        ]);
    }
}
