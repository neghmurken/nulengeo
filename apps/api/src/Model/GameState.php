<?php

declare(strict_types=1);

namespace App\Model;

use App\Exception\GameFinishedException;
use App\Exception\RoundAlreadyAnsweredException;
use App\Exception\RoundNotAnsweredException;
use LogicException;

final readonly class GameState
{
    /**
     * @param list<City>        $cities
     * @param list<RoundResult> $results
     */
    private function __construct(
        public Mode $mode,
        public array $cities,
        public int $currentRound,
        public bool $roundAnswered,
        public array $results,
        public GameStatus $status,
    ) {
    }

    /**
     * @param list<City> $cities
     */
    public static function start(Mode $mode, array $cities): self
    {
        return new self($mode, $cities, 0, false, [], GameStatus::Playing);
    }

    public function getCurrentCity(): City
    {
        return $this->cities[$this->currentRound];
    }

    public function answerRound(RoundResult $result): self
    {
        if (GameStatus::Finished === $this->status) {
            throw new GameFinishedException();
        }

        if ($this->roundAnswered) {
            throw new RoundAlreadyAnsweredException();
        }

        return new self(
            $this->mode,
            $this->cities,
            $this->currentRound,
            true,
            [...$this->results, $result],
            $this->status,
        );
    }

    public function advance(): self
    {
        if (GameStatus::Finished === $this->status) {
            throw new GameFinishedException();
        }

        if (!$this->roundAnswered) {
            throw new RoundNotAnsweredException();
        }

        $isLastRound = $this->currentRound + 1 >= count($this->cities);

        return new self(
            $this->mode,
            $this->cities,
            $isLastRound ? $this->currentRound : $this->currentRound + 1,
            false,
            $this->results,
            $isLastRound ? GameStatus::Finished : GameStatus::Playing,
        );
    }

    public function getTotalScore(): int
    {
        return array_sum(array_map(static fn (RoundResult $result): int => $result->score, $this->results));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if (GameStatus::Finished === $this->status) {
            return [
                'status' => $this->status->value,
                'totalScore' => $this->getTotalScore(),
                'results' => array_map(
                    fn (RoundResult $result): array => [
                        'inseeCode' => $result->inseeCode,
                        'name' => $this->getCityByInseeCode($result->inseeCode)->name,
                        'distanceKm' => $result->distanceKm,
                        'score' => $result->score,
                    ],
                    $this->results,
                ),
            ];
        }

        $city = $this->getCurrentCity();
        $payload = [
            'status' => $this->status->value,
            'mode' => $this->mode->value,
            'round' => $this->currentRound + 1,
            'totalRounds' => count($this->cities),
            'city' => ['name' => $city->name, 'population' => $city->population],
            'runningScore' => $this->getTotalScore(),
        ];

        if ($this->roundAnswered) {
            $result = $this->results[array_key_last($this->results)];
            $payload['guess'] = ['latitude' => $result->guessPosition->latitude, 'longitude' => $result->guessPosition->longitude];
            $payload['actual'] = ['latitude' => $city->latitude, 'longitude' => $city->longitude];
            $payload['distanceKm'] = $result->distanceKm;
            $payload['score'] = $result->score;
        }

        return $payload;
    }

    private function getCityByInseeCode(string $inseeCode): City
    {
        foreach ($this->cities as $city) {
            if ($city->inseeCode === $inseeCode) {
                return $city;
            }
        }

        throw new LogicException(sprintf('City "%s" not found among the drawn cities.', $inseeCode));
    }
}
