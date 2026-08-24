<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CurrentGameTest extends WebTestCase
{
    public function testAFreshSessionIsIdle(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/games/current');

        self::assertResponseIsSuccessful();
        self::assertSame(['status' => 'idle'], json_decode($client->getResponse()->getContent(), true));
    }

    public function testAStartedGameIsReportedAsPlaying(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"easy"}');

        $client->request('GET', '/api/games/current');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);

        self::assertSame('playing', $payload['status']);
        self::assertSame(1, $payload['round']);
    }
}
