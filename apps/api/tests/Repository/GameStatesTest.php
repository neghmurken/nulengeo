<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Exception\NoActiveGameException;
use App\Model\GameState;
use App\Model\Mode;
use App\Repository\GameStates;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class GameStatesTest extends TestCase
{
    private GameStates $gameStates;

    protected function setUp(): void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->gameStates = new GameStates($requestStack);
    }

    public function testLoadWithoutAStoredGameThrows(): void
    {
        $this->expectException(NoActiveGameException::class);

        $this->gameStates->load();
    }

    public function testSaveThenLoadRoundTripsTheSameState(): void
    {
        $state = GameState::start(Mode::Easy, []);

        $this->gameStates->save($state);

        self::assertSame($state, $this->gameStates->load());
    }
}
