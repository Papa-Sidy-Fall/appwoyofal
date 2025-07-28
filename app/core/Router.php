<?php

namespace DevNoKage;

use DevNoKage\Enums\KeyRoute;

class Router
{

    private static array $routes = [];

    public static function resolve(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes as $route => $info) {
            // Gestion des routes avec méthode HTTP (nouveau format)
            if (strpos($route, ':') !== false) {
                [$routeMethod, $routePath] = explode(':', $route, 2);
                if ($routeMethod !== $method) {
                    continue;
                }
                $route = $routePath;
            }

            preg_match_all('/\{(\w+)\}/', $route, $paramNames);
            $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $params = array_combine($paramNames[1], $matches);

                // Nouveau format avec callback
                if (is_callable($info)) {
                    call_user_func_array($info, $params);
                    return;
                }

                // Ancien format avec tableau
                if (is_array($info)) {
                    $controllerName = $info[KeyRoute::CONTROLLER->value];
                    $action = $info[KeyRoute::METHOD->value];

                    if (isset($info[KeyRoute::MIDDLEWARE->value]) && is_array($info[KeyRoute::MIDDLEWARE->value])) {
                        foreach ($info[KeyRoute::MIDDLEWARE->value] as $middlewareClass) {
                            if (class_exists($middlewareClass) && method_exists($middlewareClass, '__invoke')) {
                                $middleware = new $middlewareClass();
                                $middleware();
                            }
                        }
                    }

                    $controller = new $controllerName();
                    call_user_func_array([$controller, $action], $params);
                    return;
                }
            }
        }
            // header("Location: /404");
        http_response_code(404);
        echo "Page non trouvée";
        exit;
    }

    public static function setRoute(array $route): void
    {
        if (!is_array($route) || $route === []) {
            throw new \Exception("Veillez donnés le tableau des routes ca dois etre un table non vide !", 1);
        }
        self::$routes = $route;
    }

    public static function get(string $route, callable $callback): void
    {
        self::addRoute('GET', $route, $callback);
    }

    public static function post(string $route, callable $callback): void
    {
        self::addRoute('POST', $route, $callback);
    }

    public static function put(string $route, callable $callback): void
    {
        self::addRoute('PUT', $route, $callback);
    }

    public static function delete(string $route, callable $callback): void
    {
        self::addRoute('DELETE', $route, $callback);
    }

    private static function addRoute(string $method, string $route, callable $callback): void
    {
        self::$routes[$method . ':' . $route] = $callback;
    }
}
