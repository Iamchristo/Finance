<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = []): Response
    {
        return Response::html(View::render($template, $data));
    }

    protected function redirect(string $location): Response
    {
        return Response::redirect($location);
    }

    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function back(Request $request, string $fallback = '/'): Response
    {
        return Response::redirect($request->header('Referer') ?? $fallback);
    }

    protected function currentUserId(): ?int
    {
        return Session::userId();
    }
}
