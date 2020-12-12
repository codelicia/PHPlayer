<?php

declare(strict_types=1);

namespace PHPlayer;

use PHPlayer\MusicPlayer\Player\MusicPlayer;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function array_search;
use function assert;
use function file_get_contents;
use function json_decode;
use function preg_match;
use function sleep;
use function sprintf;
use function trim;
use function var_export;

final class Player extends Command
{
    private string $genre = 'Rock';

    private array $info = [];

    protected static $defaultName = 'default';

    private MusicPlayer $player;

    public function __construct(MusicPlayer $player)
    {
        parent::__construct();

        $this->player = $player;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(ASCII\Art::intro());

        $helper = $this->getHelper('question');
        assert($helper instanceof QuestionHelper);
        $question = new Question('> ', '?');
        $question->setAutocompleterValues(['play', 'stop', 'next', 'exit', 'genres', 'info']);
        $userInput = $helper->ask($input, $output, $question);

        if ($userInput === 'genres') {
            $output->writeln((new ASCII\Art())->genresList());
        }

        if (preg_match('/play ([\w\-\s]+)/', $userInput, $genre)) {
            $reflactor = new ReflectionClass(new Genres\Allowed());

            if (array_search($genre[1], $reflactor->getConstants(), false) !== false) {
                $this->genre = $genre[1];
                $this->playMusic($output, $genre[1]);
            } else {
                $output->writeln('<info>• I\'m sorry, genre not found. </info>');
            }
        }

        if ($userInput === 'next') {
            $this->playMusic($output, $this->genre);
        }

        if ($userInput === 'info') {
            $output->writeln(var_export($this->genre, true));
            $output->writeln(var_export($this->info, true));
        }

        if ($userInput === 'stop') {
            $this->player->stop();
        }

        if ($userInput === 'exit') {
            return 1;
        }

        return $this->execute($input, $output);
    }

    protected function playMusic(OutputInterface $output, $genre)
    {
        $info = json_decode(file_get_contents('https://cmd.to/api/v1/apps/fm/genres/' . $genre . '?limit=1'), true);

        // Find streamable tracks
        $i = 0;
        while ($info[$i]['is_streamable'] === false) {
            $i++;
        }

        $this->info = $info[$i];
        $stream     = $info[$i]['stream_url'];

        $output->writeln('Playing: ' . $info[$i]['title']);

        // Because of a bug on iTunes, we should stop the music before playing another one
        $this->player->stop();

        // Or Itunes
        `osascript -e 'tell application "Music"
          open location "$stream?client_id=26fb3c513c8e0e2c18a75e6174f4ca70"
          play
          set visible of every window to false
          end tell'`;

        // Wait to get status. Probably can do it with osascript as well
        sleep(2);

        $time     = `osascript -e 'tell application "Music" to time of current track as string'`;
        $duration = `osascript -e 'tell application "Music" to duration of current track as string'`;
        $position = `osascript -e 'tell application "Music" to player position as string'`;

        $output->writeln(sprintf('Time: %s - Duration: %s - Position: %s', trim($time), trim($duration), trim($position)));
        $output->writeln('<info>Playing track.</info>');
    }
}
