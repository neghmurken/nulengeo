<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\GameStates;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class AbandonGame
{
    public function __construct(private GameStates $gameStates)
    {
    }

    #[Route('/api/games/current', name: 'api_games_abandon', methods: ['DELETE'])]
    public function __invoke(): JsonResponse
    {
        $this->gameStates->clear();

        return new JsonResponse(['status' => 'idle']);
    }
}
