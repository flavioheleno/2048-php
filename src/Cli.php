<?php
declare(strict_types = 1);

namespace TwentyFourtyEight;

use RuntimeException;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Game CLI
 */
final class Cli extends Command {
  protected static $defaultName = 'game:new';
  /**
   * @var bool
   */
  private bool $mustInterrupt = false;

  public function signalHandler(int $signal): void {
    if (\in_array($signal, [\SIGTERM, \SIGINT])) {
      $this->mustInterrupt = true;
    }
  }

  /**
   * Command configuration.
   *
   * @return void
   */
  protected function configure() {
    $this
      ->setDescription('Start a new 2048 game.');
  }

  /**
   * Command execution.
   *
   * @param \Symfony\Component\Console\Input\InputInterface   $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    try {
      $io = new SymfonyStyle($input, $output);
      $io->title(
        \sprintf(
          '%s v%s - Game CLI',
          $this->getApplication()->getName(),
          $this->getApplication()->getVersion(),
        )
      );
      $io->text(\sprintf('Started with pid <options=bold;fg=cyan>%d</>', \posix_getpid()));
      \pcntl_async_signals(true);
      \pcntl_signal(SIGTERM, [$this, 'signalHandler']);
      \pcntl_signal(SIGINT, [$this, 'signalHandler']);

      $events = [];

      $io->section('New game');
      $timer = new Timer();
      $timer->start();
      $output = $output->section();
      $io = new SymfonyStyle($input, $output);
      $board = Factory::createBoard();
      $board->getEmitter()
        ->on(
          'block.merged',
          function (string $orientation) use (&$events) {
            $events[] = sprintf('[%s] block.merged:%s', date('H:i:s'), $orientation);
          }
        )
        ->on(
          'block.moved',
          function (string $orientation) use (&$events) {
            $events[] = sprintf('[%s] block.moved:%s', date('H:i:s'), $orientation);
          }
        );

      $game = new Game($board);
      $moveCount = 0;
      do {
        $output->clear();
        if ($board->countEmpty() > 0) {
          $board->spawnValue();
        }

        foreach (Printer::toArray($board) as $line) {
          $io->text($line);
        }

        $io->newLine();
        $io->text(
          sprintf(
            'Score: <options=bold;fg=cyan>%d</>',
            $game->getScore()
          )
        );
        $io->text(
          sprintf(
            'Max: <options=bold;fg=cyan>%d</>',
            $game->maxValue()
          )
        );
        $io->text(
          sprintf(
            'Min: <options=bold;fg=cyan>%d</>',
            $game->minValue()
          )
        );
        $io->text(
          sprintf(
            'Empty: <options=bold;fg=cyan>%d</>',
            $board->countEmpty()
          )
        );
        $io->text(
          sprintf(
            'Moves: <options=bold;fg=cyan>%d</>',
            $moveCount++
          )
        );
        $io->newLine();
        if ($game->maxValue() >= 2048) {
          break;
        }

        $move = Strategy::probe(
          $game,
          function (Game $game): float {
            return $game->maxValue() * 0.1 +
              $game->minValue() * 0.0 +
              $game->getBoard()->countEmpty() * 0.1 +
              $game->getLastMergeScore() * 0.8;
          },
          10
        );
        $io->text(sprintf('Move: <options=bold;fg=cyan>%s</>', $move));

        switch ($move) {
          case 'up':
            $board->moveUp();
            break;
          case 'right':
            $board->moveRight();
            break;
          case 'down':
            $board->moveDown();
            break;
          case 'left':
            $board->moveLeft();
            break;
        }

      } while ($game->isFinished() === false && $moveCount < 5000);
    } catch (Exception $exception) {
      $io->error($exception->getMessage());
      if ($output->isDebug()) {
        $io->listing(\explode(\PHP_EOL, $exception->getTraceAsString()));
      }

      return 1;
    } finally {
      $formatter = new ResourceUsageFormatter();
      $io->text(
        sprintf(
          'Game play: <options=bold;fg=cyan>%s</>',
          $formatter->resourceUsage($timer->stop())
        )
      );
    }

    return 0;
  }
}
