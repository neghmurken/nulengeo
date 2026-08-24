<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\GameStateException;
use App\Http\Problem;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class GameExceptionListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof GameStateException) {
            return;
        }

        $event->setResponse(Problem::fromGameStateException($exception));
    }
}
