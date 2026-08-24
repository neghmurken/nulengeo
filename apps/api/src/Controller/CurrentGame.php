<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\NoActiveGameException;
use App\Repository\GameStates;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CurrentGame
{
    public function __construct(private GameStates $gameStates)
    {
    }

    #[Route('/api/games/current', name: 'api_games_current', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        try {
            $state = $this->gameStates->load();
        } catch (NoActiveGameException) {
            return new JsonResponse(['status' => 'idle']);
        }

        return new JsonResponse($state->toArray());
    }
}
