<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class NextRoundTest extends GameControllerTestCase
{
    public function testAdvancingAfterAGuessMovesToRoundTwo(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"easy"}');
        $client->request(
            'POST',
            '/api/games/guess',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"latitude":46.5,"longitude":2.5}',
        );

        $client->request('POST', '/api/games/next');

        self::assertResponseIsSuccessful();
        $payload = $this->decodeJson($client->getResponse());

        self::assertSame('playing', $payload['status']);
        self::assertSame(2, $payload['round']);
        self::assertArrayNotHasKey('guess', $payload);
    }

    public function testAdvancingBeforeAnsweringIsRejected(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/games', server: ['CONTENT_TYPE' => 'application/json'], content: '{"mode":"easy"}');

        $client->request('POST', '/api/games/next');

        self::assertResponseStatusCodeSame(409);
        $payload = $this->decodeJson($client->getResponse());

        self::assertSame('round_not_answered', $payload['type']);
    }
}
