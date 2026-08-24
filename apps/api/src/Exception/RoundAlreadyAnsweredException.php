<?php

declare(strict_types=1);

namespace App\Exception;

final class RoundAlreadyAnsweredException extends GameStateException
{
    public function __construct()
    {
        parent::__construct('This round has already been answered.');
    }

    public function getType(): string
    {
        return 'round_already_answered';
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}
