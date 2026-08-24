<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

abstract class GameStateException extends RuntimeException
{
    abstract public function getType(): string;

    abstract public function getStatusCode(): int;
}
