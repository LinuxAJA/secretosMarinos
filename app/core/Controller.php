<?php
/**
 * ============================================================================
 * Controller.php — Controlador base
 * ============================================================================
 * Todos los controladores (HomeController, AuthController, etc.)
 * heredarán de esta clase para reutilizar:
 *   - render(): cargar una vista dentro del layout
 *   - redirect(): redirigir a otra URL
 *   - json(): responder JSON (útil para AJAX futuro)
 * ============================================================================
 */

namespace App\Core;

abstract class Controller
{
    /**
     * Renderiza una vista PHP dentro del layout principal.
     *
     * @param string $view    Ruta relativa dentro de /views, sin .php
     *                        Ej: 'pages/home' → views/pages/home.php
     * @param array  $data    Variables disponibles en la vista
     * @param string $layout  Layout a usar (por defecto 'main')
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extrae el array $data a variables locales:
        // ['titulo' => 'Hola'] → $titulo = 'Hola'
        extract($data);

        // Ruta completa del archivo de vista
        $viewFile = VIEWS_PATH . '/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'Vista no encontrada: ' . htmlspecialchars($view);
            return;
        }

        // Captura la salida de la vista en un buffer ($content)
        // Así el layout puede imprimirla con echo $content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Carga el layout (views/layouts/main.php)
        $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';

        if (!is_file($layoutFile)) {
            // Si no hay layout, muestra solo el contenido de la vista
            echo $content;
            return;
        }

        require $layoutFile;
    }

    /**
     * Redirige al navegador a otra ruta de la app.
     *
     * @param string $path  Ruta relativa, ej: '/login'
     */
    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    /**
     * Responde con JSON (útil más adelante para Fetch API).
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
