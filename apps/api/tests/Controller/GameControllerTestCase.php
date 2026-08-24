<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class GameControllerTestCase extends WebTestCase
{
    /**
     * @return array<string, mixed>
     */
    protected function decodeJson(Response $response): array
    {
        $content = $response->getContent();

        if (false === $content) {
            throw new RuntimeException('Response has no content.');
        }

        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Response content is not a JSON object.');
        }

        return $decoded;
    }
}
