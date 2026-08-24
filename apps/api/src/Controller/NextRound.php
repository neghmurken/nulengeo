<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\GameStates;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class NextRound
{
    public function __construct(private GameStates $gameStates)
    {
    }

    #[Route('/api/games/next', name: 'api_games_next', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        $state = $this->gameStates->load()->advance();
        $this->gameStates->save($state);

        return new JsonResponse($state->toArray());
    }
}
