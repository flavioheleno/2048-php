<?php
declare(strict_types = 1);

namespace TwentyFourtyEight;

use TwentyFourtyEight\Board\Board;

final class Game {
  private Board $board;
  private int $score;
  private int $lastMergeScore = 0;
  private bool $lastMoveValid = false;

  public function __construct(Board $board) {
    $this->board = $board;
    $this->score = 0;
    $emitter = $this->board->getEmitter();
    $emitter->on('block.merged', [$this, 'updateScore']);
    // $emitter->on('block.moved', [$this, 'lastMoveValid']);
  }

  public function updateScore(string $orientation, int $val, int $row, int $col): void {
    $this->score += $val;
    $this->lastMergeScore = $val;
  }

  public function getLastMergeScore(): int {
    return $this->lastMergeScore;
  }

  public function isLastMoveValid(): bool {
    return $this->lastMoveValid;
  }

  public function getScore(): int {
    return $this->score;
  }

  public function getBoard(): Board {
    return $this->board;
  }

  public function minValue(): int {
    $min = PHP_INT_MAX;
    $width = $this->board->getWidth();
    $height = $this->board->getHeight();
    for ($row = 0; $row < $height; $row++) {
      for ($col = 0; $col < $width; $col++) {
        if ($this->board->emptyAt($row, $col)) {
          continue;
        }

        $min = min($this->board->valueAt($row, $col), $min);
      }
    }

    return $min;
  }

  public function maxValue(): int {
    $max = 0;
    $width = $this->board->getWidth();
    $height = $this->board->getHeight();
    for ($row = 0; $row < $height; $row++) {
      for ($col = 0; $col < $width; $col++) {
        if ($this->board->emptyAt($row, $col)) {
          continue;
        }

        $max = max($this->board->valueAt($row, $col), $max);
      }
    }

    return $max;
  }

  public function moveUp(): self {
    $this->lastMoveValid = $this->board->moveUp();

    return $this;
  }

  public function moveRight(): self {
    $this->lastMoveValid = $this->board->moveRight();

    return $this;
  }

  public function moveDown(): self {
    $this->lastMoveValid = $this->board->moveDown();

    return $this;
  }

  public function moveLeft(): self {
    $this->lastMoveValid = $this->board->moveLeft();

    return $this;
  }

  public function isFinished(): bool {
    // if there is still space for sparwning, game is not finished
    if ($this->board->countEmpty() > 0) {
      return false;
    }

    // if the 2048 block was achieved, game is finished
    if ($this->maxValue() === 2048) {
      return true;
    }

    $board = $this->board->toArray();
    // check rows for available merges
    $height = $this->board->getHeight();
    for ($row = 0; $row < $height; $row++) {
      $probe = implode(',', array_filter($board[$row]));
      if (preg_match('/([0-9]+),\1/', $probe)) {
        return false;
      }
    }

    // check columns for available merges
    $width = $this->board->getWidth();
    for ($col = 0; $col < $width; $col++) {
      $probe = implode(',', array_filter(array_column($board, $col)));
      if (preg_match('/([0-9]+),\1/', $probe)) {
        return false;
      }
    }

    return true;
  }

  public function __clone() {
    $this->board = clone $this->board;
  }
}
