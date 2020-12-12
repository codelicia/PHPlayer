<?php

namespace PHPlayer\MusicPlayer\Player;


interface MusicPlayer
{
    public function play(/* SongStream $stream*/) :void;

    public function pause() :void;

    public function stop() :void;
}
