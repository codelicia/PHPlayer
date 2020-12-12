<?php

declare(strict_types=1);

namespace PHPlayer\MusicPlayer;

use PHPlayer\MusicPlayer\Player\Itunes;
use PHPlayer\MusicPlayer\Player\MusicPlayer;

// @todo: It should check for existing player installed in the current
//        machine and return a proper instance to manipulate the player.
final class Factory
{
    public function __invoke(): MusicPlayer
    {
        return new Itunes();
    }
} 
