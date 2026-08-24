<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class SubmitGuessTest extends GameControllerTestCase
{
    public function testGuessingRevealsTheActualPosition(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"easy"}');

        $client->request(
            'POST',
            '/api/games/guess',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"latitude":46.5,"longitude":2.5}',
        );

        self::assertResponseIsSuccessful();
        $payload = $this->decodeJson($client->getResponse());

        self::assertSame('playing', $payload['status']);
        self::assertArrayHasKey('actual', $payload);
        self::assertArrayHasKey('guess', $payload);
        self::assertSame(['latitude' => 46.5, 'longitude' => 2.5], $payload['guess']);
        self::assertArrayHasKey('distanceKm', $payload);
        self::assertArrayHasKey('score', $payload);
        self::assertSame($payload['score'], $payload['runningScore']);
    }

    public function testGuessingTwiceForTheSameRoundIsRejected(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"easy"}');
        $client->request(
            'POST',
            '/api/games/guess',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"latitude":46.5,"longitude":2.5}',
        );

        $client->request(
            'POST',
            '/api/games/guess',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"latitude":46.5,"longitude":2.5}',
        );

        self::assertResponseStatusCodeSame(409);
        $payload = $this->decodeJson($client->getResponse());

        self::assertSame('round_already_answered', $payload['type']);
    }
}
