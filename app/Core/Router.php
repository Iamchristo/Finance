<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Middleware\MiddlewareInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

final class Router
{
    /** @var array<int, array{method: string, path: string, controller: string, action: string, middleware: string[]}> */
    private array $routes = [];

    /** @var array<int, array{prefix: string, middleware: string[]}> */
    private array $groupStack = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, array $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * @param array{prefix?: string, middleware?: string[]} $attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = [
            'prefix' => $attributes['prefix'] ?? '',
            'middleware' => $attributes['middleware'] ?? [],
        ];

        $callback($this);

        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $prefix = '';
        $middleware = [];

        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'];
            $middleware = [...$middleware, ...$group['middleware']];
        }

        $fullPath = rtrim($prefix, '/') . '/' . ltrim($path, '/');
        $fullPath = $fullPath === '' ? '/' : $fullPath;

        if ($fullPath !== '/' && str_ends_with($fullPath, '/')) {
            $fullPath = rtrim($fullPath, '/');
        }

        $middleware = [...$middleware, ...($handler['middleware'] ?? [])];

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'controller' => $handler['controller'] ?? $handler[0],
            'action' => $handler['action'] ?? $handler[1] ?? '__invoke',
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request, Container $container): Response
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            foreach ($this->routes as $index => $route) {
                $r->addRoute($route['method'], $route['path'], $index);
            }
        });

        $routeInfo = $dispatcher->dispatch($request->method(), $request->path());

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND => Response::html('404 Not Found', 404),
            Dispatcher::METHOD_NOT_ALLOWED => Response::html('405 Method Not Allowed', 405),
            Dispatcher::FOUND => $this->handleFound($routeInfo, $request, $container),
        };
    }

    private function handleFound(array $routeInfo, Request $request, Container $container): Response
    {
        $route = $this->routes[$routeInfo[1]];

        foreach ($routeInfo[2] as $key => $value) {
            $request->setAttribute($key, $value);
        }

        $handler = function (Request $request) use ($route, $container): Response {
            $controller = $container->make($route['controller']);
            $action = $route['action'];

            return $controller->$action($request);
        };

        foreach (array_reverse($route['middleware']) as $entry) {
            [$middlewareClass, $args] = $this->parseMiddleware($entry);
            $next = $handler;
            $handler = function (Request $request) use ($middlewareClass, $args, $next, $container): Response {
                /** @var MiddlewareInterface $middleware */
                $middleware = $container->make($middlewareClass);

                return $middleware->handle($request, $next, ...$args);
            };
        }

        return $handler($request);
    }

    /** @return array{0: string, 1: string[]} */
    private function parseMiddleware(string $entry): array
    {
        if (str_contains($entry, ':')) {
            [$class, $argString] = explode(':', $entry, 2);

            return [$class, explode(',', $argString)];
        }

        return [$entry, []];
    }
}
