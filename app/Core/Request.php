<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @var array<string, mixed> */
    private array $query;

    /** @var array<string, mixed> */
    private array $body;

    /** @var array<string, mixed> */
    private array $attributes = [];

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        array $query,
        array $body,
        private readonly array $server,
    ) {
        $this->query = $query;
        $this->body = $body;
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        $body = $_POST;

        if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true) && empty($body)) {
            parse_str(file_get_contents('php://input') ?: '', $body);
        }

        // Allow HTML forms to spoof PUT/PATCH/DELETE via _method field.
        if ($method === 'POST' && isset($body['_method'])) {
            $method = strtoupper((string) $body['_method']);
        }

        return new self($method, $uriPath, $_GET, $body, $_SERVER);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . str_replace('-', '_', strtoupper($name));

        return $this->server[$key] ?? null;
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function isHtmx(): bool
    {
        return $this->header('HX-Request') === 'true';
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
