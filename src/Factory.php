<?php
declare(strict_types = 1);

namespace TwentyFourtyEight;

use Evenement\EventEmitter;
use TwentyFourtyEight\Board\Board;

final class Factory {
  public static function createBoard(): Board {
    return new Board(new EventEmitter());
  }

  public static function createGame(): Game {
    return new Game(self::createBoard());
  }
}
