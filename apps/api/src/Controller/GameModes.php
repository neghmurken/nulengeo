<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Mode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GameModes
{
    #[Route('/api/games/modes', name: 'api_games_modes', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['modes' => array_column(Mode::cases(), 'value')]);
    }
}
