<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class GameModesTest extends GameControllerTestCase
{
    public function testListsAllAvailableModes(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/games/modes');

        self::assertResponseIsSuccessful();
        self::assertSame(
            ['modes' => ['easy', 'medium', 'hard', 'expert']],
            $this->decodeJson($client->getResponse()),
        );
    }
}
