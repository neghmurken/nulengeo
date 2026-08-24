<?php

declare(strict_types=1);

namespace App\Exception;

final class NoActiveGameException extends GameStateException
{
    public function __construct()
    {
        parent::__construct('No active game in session.');
    }

    public function getType(): string
    {
        return 'no_active_game';
    }

    public function getStatusCode(): int
    {
        return 404;
    }
}
