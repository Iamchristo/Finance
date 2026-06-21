<?php

declare(strict_types=1);

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

require dirname(__DIR__) . '/bootstrap.php';

// Fall back to a brick/math-backed bcmath polyfill only when the real
// extension isn't installed (e.g. this sandbox); inert wherever it is.
if (!function_exists('bcadd')) {
    function bcadd(string $a, string $b, int $scale = 0): string
    {
        return BigDecimal::of($a)->plus(BigDecimal::of($b))->toScale($scale, RoundingMode::Down)->__toString();
    }

    function bcsub(string $a, string $b, int $scale = 0): string
    {
        return BigDecimal::of($a)->minus(BigDecimal::of($b))->toScale($scale, RoundingMode::Down)->__toString();
    }

    function bcmul(string $a, string $b, int $scale = 0): string
    {
        return BigDecimal::of($a)->multipliedBy(BigDecimal::of($b))->toScale($scale, RoundingMode::Down)->__toString();
    }

    function bcdiv(string $a, string $b, int $scale = 0): string
    {
        return BigDecimal::of($a)->toScale($scale + 4, RoundingMode::Down)
            ->dividedBy(BigDecimal::of($b), $scale, RoundingMode::Down)
            ->__toString();
    }

    function bccomp(string $a, string $b, int $scale = 0): int
    {
        $left = BigDecimal::of($a)->toScale($scale, RoundingMode::Down);
        $right = BigDecimal::of($b)->toScale($scale, RoundingMode::Down);

        return $left->compareTo($right);
    }
}
