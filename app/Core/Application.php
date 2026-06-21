<?php

declare(strict_types=1);

namespace App\Core;

final class Application
{
    private readonly Router $router;
    private readonly Container $container;

    public function __construct()
    {
        $this->router = new Router();
        $this->container = new Container();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function loadRoutes(string $routesPath): void
    {
        $router = $this->router;
        foreach (glob($routesPath . '/*.php') as $file) {
            (function (string $file) use ($router): void {
                require $file;
            })($file);
        }
    }

    public function run(): void
    {
        Session::start();
        $request = Request::capture();

        try {
            $response = $this->router->dispatch($request, $this->container);
        } catch (\Throwable $e) {
            Logger::exception($e);
            $response = Response::html(View::render('errors/500'), 500);
        }

        $response->send();
    }
}
