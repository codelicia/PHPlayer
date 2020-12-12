<?php

declare(strict_types=1);

namespace PHPlayer\MusicPlayer\Player;

use function sprintf;

final class Itunes implements MusicPlayer
{
    public function play(): void
    {
    }

    public function pause(): void
    {
        $this->executeOSScript('pause');
    }

    public function stop(): void
    {
        $this->executeOSScript('stop');
    }

    private function executeOSScript(string $command): void
    {
        $format = 'osascript -e \'tell application "Music"
            %s
        end tell\'';

        $script = sprintf($format, $command);

        // @todo, execute it via proper proc handler,
        //        not via backtick.
        `$script`;
    }
}
