<?php

declare(strict_types=1);

namespace App\Exception;

final class GameFinishedException extends GameStateException
{
    public function __construct()
    {
        parent::__construct('This game has already finished.');
    }

    public function getType(): string
    {
        return 'game_finished';
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}
