<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Problem;
use App\Model\Position;
use App\Model\RoundResult;
use App\Repository\GameStates;
use App\Service\Score\Calculator;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SubmitGuess
{
    public function __construct(
        private GameStates $gameStates,
        private Calculator $calculator,
    ) {
    }

    #[Route('/api/games/guess', name: 'api_games_guess', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $body = $request->toArray();
        } catch (JsonException) {
            return Problem::create('invalid_guess', 400, 'The request body must be valid JSON.');
        }

        $latitude = $body['latitude'] ?? null;
        $longitude = $body['longitude'] ?? null;

        if (!$this->isValidCoordinate($latitude, $longitude)) {
            return Problem::create(
                'invalid_guess',
                400,
                'The "latitude"/"longitude" fields must be numbers within -90..90 / -180..180.',
            );
        }

        $state = $this->gameStates->load();
        $city = $state->getCurrentCity();
        $guessPosition = new Position((float) $latitude, (float) $longitude);
        $actualPosition = new Position($city->latitude, $city->longitude);
        $distanceKm = $guessPosition->toDistance($actualPosition);

        $result = new RoundResult(
            $city->inseeCode,
            $guessPosition,
            $distanceKm,
            $this->calculator->computeScore($distanceKm),
        );

        $state = $state->answerRound($result);
        $this->gameStates->save($state);

        return new JsonResponse($state->toArray());
    }

    private function isValidCoordinate(mixed $latitude, mixed $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude)
            && (float) $latitude >= -90.0 && (float) $latitude <= 90.0
            && (float) $longitude >= -180.0 && (float) $longitude <= 180.0;
    }
}
