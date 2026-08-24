<?php

declare(strict_types=1);

namespace App\Model;

enum GameStatus: string
{
    case Playing = 'playing';
    case Finished = 'finished';
}
