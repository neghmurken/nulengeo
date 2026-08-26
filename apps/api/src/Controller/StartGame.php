<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Problem;
use App\Model\GameState;
use App\Model\Mode;
use App\Repository\Cities;
use App\Repository\GameStates;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class StartGame
{
    public function __construct(
        private Cities $cities,
        private GameStates $gameStates,
        #[Autowire('%app.game_round_count%')]
        private int $roundCount,
        #[Autowire('%app.game_max_score%')]
        private int $maxScorePerRound,
    ) {
    }

    #[Route('/api/games', name: 'api_games_start', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $body = $request->toArray();
        } catch (JsonException) {
            return Problem::create('invalid_mode', 400, 'The request body must be valid JSON.');
        }

        $mode = Mode::tryFrom($body['mode'] ?? '');

        if (null === $mode) {
            $validModes = implode(', ', array_column(Mode::cases(), 'value'));

            return Problem::create('invalid_mode', 400, sprintf('The "mode" field must be one of: %s.', $validModes));
        }

        $cities = $this->cities->draw($mode->toTier(), $this->roundCount);
        $state = GameState::start($mode, $cities);
        $this->gameStates->save($state);

        return new JsonResponse($state->toArray($this->maxScorePerRound));
    }
}
