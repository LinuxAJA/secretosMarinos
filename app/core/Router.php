<?php
/**
 * ============================================================================
 * Router.php — Enrutador simple estilo MVC
 * ============================================================================
 * Traduce una URL amigable a un Controller@método.
 *
 * Ejemplo:
 *   GET /especies  →  SpeciesController::index()
 *   GET /especies/3 → SpeciesController::show(3)
 *
 * Flujo:
 *   1. Se registran rutas con get()/post()
 *   2. dispatch($uri, $method) busca coincidencia
 *   3. Instancia el controlador y llama al método
 * ============================================================================
 */

namespace App\Core;

class Router
{
    /**
     * Rutas registradas.
     * Estructura:
     *   [
     *     'GET' => [
     *        '/' => ['controller' => HomeController::class, 'action' => 'index'],
     *        ...
     *     ],
     *     'POST' => [ ... ]
     *   ]
     */
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Registra una ruta GET (mostrar páginas, listados, formularios).
     *
     * @param string $path   Ruta relativa, ej: '/login' o '/especies/{id}'
     * @param array  $handler [ClaseController::class, 'metodo']
     */
    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    /**
     * Registra una ruta POST (enviar formularios: login, crear, editar).
     */
    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    /**
     * Busca la ruta y ejecuta el controlador correspondiente.
     *
     * @param string $uri    Path de la petición (sin query string)
     * @param string $method GET o POST
     */
    public function dispatch(string $uri, string $method): void
    {
        $method = strtoupper($method);
        $uri = $this->normalize($uri);

        // Si el método HTTP no está soportado
        if (!isset($this->routes[$method])) {
            $this->abort(405, 'Método no permitido');
            return;
        }

        // 1) Coincidencia exacta (sin parámetros dinámicos)
        if (isset($this->routes[$method][$uri])) {
            $this->call($this->routes[$method][$uri], []);
            return;
        }

        // 2) Coincidencia con parámetros {id}, {slug}, etc.
        foreach ($this->routes[$method] as $route => $handler) {
            // Convierte '/especies/{id}' en expresión regular:
            // '#^/especies/(?P<id>[^/]+)$#'
            $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Solo nos quedamos con los grupos con nombre (id, slug...)
                $params = array_filter(
                    $matches,
                    fn($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );

                $this->call($handler, $params);
                return;
            }
        }

        // Ninguna ruta coincidió
        $this->abort(404, 'Página no encontrada');
    }

    /**
     * Instancia el controlador y ejecuta el método con los parámetros.
     *
     * @param array $handler [Clase, 'metodo']
     * @param array $params  Parámetros de la URL (ej: ['id' => '3'])
     */
    private function call(array $handler, array $params): void
    {
        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass)) {
            $this->abort(500, "Controlador no encontrado: {$controllerClass}");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->abort(500, "Método no encontrado: {$controllerClass}@{$action}");
            return;
        }

        // Llama al método pasando los valores del array como argumentos
        call_user_func_array([$controller, $action], array_values($params));
    }

    /**
     * Normaliza rutas: asegura slash inicial y sin slash final (salvo '/').
     * '/especies/' → '/especies'
     * '' → '/'
     */
    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * Respuesta de error simple (luego se puede mejorar con vistas 404).
     */
    private function abort(int $code, string $message): void
    {
        http_response_code($code);
        // Usa vista si existe; si no, mensaje plano
        $view = VIEWS_PATH . '/pages/errors/' . $code . '.php';
        if (is_file($view)) {
            require $view;
            return;
        }

        echo "<h1>{$code}</h1><p>" . htmlspecialchars($message) . '</p>';
    }
}
