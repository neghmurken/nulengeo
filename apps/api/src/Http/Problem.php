<?php

declare(strict_types=1);

namespace App\Http;

use App\Exception\GameStateException;
use Symfony\Component\HttpFoundation\JsonResponse;

final class Problem extends JsonResponse
{
    public static function create(string $type, int $status, string $title): self
    {
        return new self(
            ['type' => $type, 'title' => $title, 'status' => $status],
            $status,
            ['Content-Type' => 'application/problem+json'],
        );
    }

    public static function fromGameStateException(GameStateException $exception): self
    {
        return self::create($exception->getType(), $exception->getStatusCode(), $exception->getMessage());
    }
}
