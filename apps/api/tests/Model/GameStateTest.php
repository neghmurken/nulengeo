<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Exception\GameFinishedException;
use App\Exception\RoundAlreadyAnsweredException;
use App\Exception\RoundNotAnsweredException;
use App\Model\City;
use App\Model\GameState;
use App\Model\GameStatus;
use App\Model\Mode;
use App\Model\Position;
use App\Model\RoundResult;
use PHPUnit\Framework\TestCase;

final class GameStateTest extends TestCase
{
    private City $angers;
    private City $lyon;

    protected function setUp(): void
    {
        $this->angers = new City('49007', 'Angers', 159022, 47.4713, -0.5474, 42.7);
        $this->lyon = new City('69123', 'Lyon', 522250, 45.7640, 4.8357, 47.9);
    }

    public function testStartCreatesAnUnansweredFirstRound(): void
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);

        self::assertSame(Mode::Easy, $state->mode);
        self::assertSame(0, $state->currentRound);
        self::assertFalse($state->roundAnswered);
        self::assertSame([], $state->results);
        self::assertSame(GameStatus::Playing, $state->status);
        self::assertSame($this->angers, $state->getCurrentCity());
    }

    public function testAnswerRoundRecordsTheResultAndMarksItAnswered(): void
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);
        $result = new RoundResult('49007', new Position(47.5, -0.5), 3.2, 900, 2.0);

        $state = $state->answerRound($result);

        self::assertTrue($state->roundAnswered);
        self::assertSame([$result], $state->results);
        self::assertSame(900, $state->getTotalScore());
    }

    public function testAnswerRoundTwiceIsRejected(): void
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);
        $state = $state->answerRound(new RoundResult('49007', new Position(47.5, -0.5), 3.2, 900, 2.0));

        $this->expectException(RoundAlreadyAnsweredException::class);

        $state->answerRound(new RoundResult('49007', new Position(47.5, -0.5), 3.2, 900, 2.0));
    }

    public function testAdvanceBeforeAnsweringIsRejected(): void
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);

        $this->expectException(RoundNotAnsweredException::class);

        $state->advance();
    }

    public function testAdvanceMovesToTheNextUnansweredRound(): void
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);
        $state = $state->answerRound(new RoundResult('49007', new Position(47.5, -0.5), 3.2, 900, 2.0));

        $state = $state->advance();

        self::assertSame(1, $state->currentRound);
        self::assertFalse($state->roundAnswered);
        self::assertSame(GameStatus::Playing, $state->status);
        self::assertSame($this->lyon, $state->getCurrentCity());
    }

    public function testAdvancingPastTheLastRoundFinishesTheGame(): void
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);
        $state = $state->answerRound(new RoundResult('49007', new Position(47.5, -0.5), 3.2, 900, 2.0))->advance();
        $state = $state->answerRound(new RoundResult('69123', new Position(45.8, 4.8), 1.1, 950, 3.0));

        $state = $state->advance();

        self::assertSame(GameStatus::Finished, $state->status);
        self::assertSame(1850, $state->getTotalScore());
    }

    public function testAnsweringAFinishedGameIsRejected(): void
    {
        $state = $this->finishedGame();

        $this->expectException(GameFinishedException::class);

        $state->answerRound(new RoundResult('69123', new Position(45.8, 4.8), 1.1, 950, 3.0));
    }

    public function testAdvancingAFinishedGameIsRejected(): void
    {
        $state = $this->finishedGame();

        $this->expectException(GameFinishedException::class);

        $state->advance();
    }

    public function testToArrayForAnUnansweredRoundOmitsTheReveal(): void
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);

        $payload = $state->toArray(1000);

        self::assertSame([
            'status' => 'playing',
            'mode' => 'easy',
            'round' => 1,
            'totalRounds' => 2,
            'city' => ['name' => 'Angers', 'population' => 159022],
            'runningScore' => 0,
        ], $payload);
    }

    public function testToArrayForAnAnsweredRoundIncludesTheReveal(): void
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);
        $state = $state->answerRound(new RoundResult('49007', new Position(47.5, -0.5), 3.2, 900, 2.0));

        $payload = $state->toArray(1000);

        self::assertSame([
            'status' => 'playing',
            'mode' => 'easy',
            'round' => 1,
            'totalRounds' => 2,
            'city' => ['name' => 'Angers', 'population' => 159022],
            'runningScore' => 900,
            'guess' => ['latitude' => 47.5, 'longitude' => -0.5],
            'actual' => ['latitude' => 47.4713, 'longitude' => -0.5474],
            'distanceKm' => 3.2,
            'score' => 900,
            'toleranceKm' => 2.0,
        ], $payload);
    }

    public function testToArrayForAFinishedGameListsEveryResultWithItsCityName(): void
    {
        $state = $this->finishedGame();

        $payload = $state->toArray(1000);

        self::assertSame('finished', $payload['status']);
        self::assertSame(1850, $payload['totalScore']);
        self::assertSame(2000, $payload['maxScore']);
        self::assertSame([
            ['inseeCode' => '49007', 'name' => 'Angers', 'distanceKm' => 3.2, 'score' => 900],
            ['inseeCode' => '69123', 'name' => 'Lyon', 'distanceKm' => 1.1, 'score' => 950],
        ], $payload['results']);
    }

    private function finishedGame(): GameState
    {
        $state = GameState::start(Mode::Easy, [$this->angers, $this->lyon]);
        $state = $state->answerRound(new RoundResult('49007', new Position(47.5, -0.5), 3.2, 900, 2.0))->advance();
        $state = $state->answerRound(new RoundResult('69123', new Position(45.8, 4.8), 1.1, 950, 3.0));

        return $state->advance();
    }
}
