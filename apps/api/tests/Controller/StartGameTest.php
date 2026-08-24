<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StartGameTest extends WebTestCase
{
    public function testStartingAGameReturnsTheFirstRound(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"easy"}');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);

        self::assertSame('playing', $payload['status']);
        self::assertSame('easy', $payload['mode']);
        self::assertSame(1, $payload['round']);
        self::assertSame(10, $payload['totalRounds']);
        self::assertSame(0, $payload['runningScore']);
        self::assertArrayHasKey('name', $payload['city']);
        self::assertArrayHasKey('population', $payload['city']);
    }

    public function testAnInvalidModeIsRejected(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"impossible"}');

        self::assertResponseStatusCodeSame(400);
        $payload = json_decode($client->getResponse()->getContent(), true);

        self::assertSame('invalid_mode', $payload['type']);
    }
}
