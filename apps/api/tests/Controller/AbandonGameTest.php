<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class AbandonGameTest extends GameControllerTestCase
{
    public function testAbandoningAStartedGameReportsIdleAfterwards(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"easy"}');

        $client->request('DELETE', '/api/games/current');

        self::assertResponseIsSuccessful();
        self::assertSame(['status' => 'idle'], $this->decodeJson($client->getResponse()));

        $client->request('GET', '/api/games/current');

        self::assertSame(['status' => 'idle'], $this->decodeJson($client->getResponse()));
    }

    public function testAbandoningWithoutAnActiveGameIsIdempotent(): void
    {
        $client = self::createClient();

        $client->request('DELETE', '/api/games/current');

        self::assertResponseIsSuccessful();
        self::assertSame(['status' => 'idle'], $this->decodeJson($client->getResponse()));
    }
}
