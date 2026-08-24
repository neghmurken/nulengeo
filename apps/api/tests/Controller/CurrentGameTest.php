<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class CurrentGameTest extends GameControllerTestCase
{
    public function testAFreshSessionIsIdle(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/games/current');

        self::assertResponseIsSuccessful();
        self::assertSame(['status' => 'idle'], $this->decodeJson($client->getResponse()));
    }

    public function testAStartedGameIsReportedAsPlaying(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"easy"}');

        $client->request('GET', '/api/games/current');

        self::assertResponseIsSuccessful();
        $payload = $this->decodeJson($client->getResponse());

        self::assertSame('playing', $payload['status']);
        self::assertSame(1, $payload['round']);
    }
}
