<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\NoActiveGameException;
use App\Model\GameState;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class GameStates
{
    private const string SESSION_KEY = 'game';

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function load(): GameState
    {
        $state = $this->requestStack->getSession()->get(self::SESSION_KEY);

        if (!$state instanceof GameState) {
            throw new NoActiveGameException();
        }

        return $state;
    }

    public function save(GameState $state): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $state);
    }
}
