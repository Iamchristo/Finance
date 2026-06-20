<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(string $section)
    {
        parent::__construct("Insufficient balance in the {$section} wallet section.");
    }
}
