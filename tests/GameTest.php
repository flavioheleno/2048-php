<?php
declare(strict_types = 1);

namespace TwentyFourtyEightTest;

use Evenement\EventEmitter;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TwentyFourtyEight\Board\Board;
use TwentyFourtyEight\Game;

class GameTest extends TestCase {
  private $emitter;

  public function setUp(): void {
    $this->emitter = $this->createMock(EventEmitter::class);
  }

  public function testMinMaxCount() {
    $board = new Board($this->emitter);
    $board->load(
      [
        [   1,    8,    9, null],
        [   2, null,   10,   14],
        [null,    6,   11,   15],
        [   4,    5, null,   16]
      ]
    );

    $game = new Game($board);
    $this->assertSame($game->minValue(), 1);
    $this->assertSame($game->maxValue(), 16);
  }
}
