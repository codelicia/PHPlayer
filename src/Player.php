<?php

declare(strict_types=1);

namespace PHPlayer;

use PHPlayer\MusicPlayer\Player\MusicPlayer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

final class Player extends Command
{
    private string $genre;

    private int $musicTime;

    protected static $defaultName = 'default';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(ASCII\Art::intro());

        $helper = $this->getHelper('question');
        $question = new Question('> ', '?');
        $userInput = $helper->ask($input, $output, $question);

        if ('genres' == $userInput) {
            $output->writeln((new ASCII\Art())->genresList());
        } elseif (preg_match('/play ([\w]+)/', $userInput, $genre)) {

            $reflactor = new \ReflectionClass(new Genres\Allowed);

            if (false !== array_search($genre[1], $reflactor->getConstants())) {
                $this->genre = $genre[1];
                $this->playMusic($genre[1]);
            } else {
                $output->writeln('<info>• I\'m sorry Dave, genre not found. </info>');
            }
        } elseif ('next' == $userInput) {
            $this->playMusic($this->genre);
        } elseif ('clear' == $userInput) {
            `clear`;
        } elseif ('exit' == $userInput) {
            return 1;
        }

        return $this->execute($input, $output);
    }

    protected function playMusic($genre)
    {
        $info = json_decode(file_get_contents('https://cmd.to/api/v1/apps/fm/genres/' . $genre . '?limit=1'));
        $stream = $info[0]->stream_url;

        $output->writeln('Playing: ' . $info[0]->title);

        // Or Itunes
        `osascript -e 'tell application "Music"
          open location "$stream?client_id=26fb3c513c8e0e2c18a75e6174f4ca70"
          play
          set visible of every window to false
          end tell'`;

        // Wait to get status. Probably can do it with osascript as well
        sleep(2);

        $time = `osascript -e 'tell application "Music" to time of current track as string'`;
        $duration = `osascript -e 'tell application "Music" to duration of current track as string'`;
        $position = `osascript -e 'tell application "Music" to player position as string'`;

        $output->writeln(\sprintf('Time: %s - Duration: %s - Position: %s', trim($time), trim($duration), trim($position)));
        $output->writeln('<info>Playing track.</info>');
    }
}
