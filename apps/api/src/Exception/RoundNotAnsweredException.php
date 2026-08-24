<?php

declare(strict_types=1);

namespace App\Exception;

final class RoundNotAnsweredException extends GameStateException
{
    public function __construct()
    {
        parent::__construct('This round has not been answered yet.');
    }

    public function getType(): string
    {
        return 'round_not_answered';
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}
