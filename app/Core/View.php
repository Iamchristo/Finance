<?php

declare(strict_types=1);

namespace App\Core;

use League\Plates\Engine;

final class View
{
    private static ?Engine $engine = null;

    public static function engine(): Engine
    {
        if (self::$engine === null) {
            self::$engine = new Engine(base_path('resources/views'));
            self::$engine->addData([
                'appName' => Config::get('app.name'),
                'simulatedBanner' => Config::get('app.simulated_banner'),
            ]);
        }

        return self::$engine;
    }

    public static function render(string $template, array $data = []): string
    {
        return self::engine()->render($template, $data);
    }
}
