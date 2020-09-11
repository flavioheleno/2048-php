<?php
declare(strict_types = 1);

namespace TwentyFourtyEight\Board;

use Evenement\EventEmitter;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;

class Board {
  private int $width;
  private int $height;
  private array $board;
  private EventEmitter $emitter;

  public function __construct(EventEmitter $emitter, int $width = 4, int $height = 4) {
    if ($width < 1) {
      throw new InvalidArgumentException('Board width cannot be smaller than 1');
    }

    if ($height < 1) {
      throw new InvalidArgumentException('Board height cannot be smaller than 1');
    }

    $this->emitter = $emitter;
    $this->width   = $width;
    $this->height  = $height;
    $this->clear();
  }

  public function load(array $board) {
    $this->clear();
    if (count($board) !== $this->height || count($board[0]) != $this->width) {
      throw new InvalidArgumentException('Cannot load a board with different dimensions');
    }

    for ($row = 0; $row < $this->height; $row++) {
      for ($col = 0; $col < $this->width; $col++) {
        $this->board[$row][$col] = $board[$row][$col];
      }
    }
  }

  public function getEmitter(): EventEmitter {
    return $this->emitter;
  }

  public function getWidth(): int {
    return $this->width;
  }

  public function getHeight(): int {
    return $this->height;
  }

  public function spawnValue(): self {
    if ($this->countEmpty() === 0) {
      throw new RuntimeException('Cannot spawn a new value');
    }

    $spawned = false;
    mt_srand((int)microtime(true) * 1000000);
    while ($spawned === false) {
      $row = mt_rand(1, $this->height) - 1;
      $col = mt_rand(1, $this->width) - 1;
      if ($this->board[$row][$col] === null) {
        $this->board[$row][$col] = 2;
        $this->emitter->emit(
          'value.spanwed',
          [
            'row' => $row,
            'col' => $col
          ]
        );
        $spawned = true;
      }
    }

    return $this;
  }

  public function emptyAt(int $row, int $col): bool {
    return $this->valueAt($row, $col) === 0;
  }

  public function valueAt(int $row, int $col): int {
    if ($row < 0 || $row >= $this->width) {
      throw new OutOfBoundsException(
        sprintf(
          '$row value cannot be smaller than 0 nor higher than %d',
          $this->width
        )
      );
    }

    if ($col < 0 || $col >= $this->height) {
      throw new OutOfBoundsException(
        sprintf(
          '$col value cannot be smaller than 0 nor higher than %d',
          $this->height
        )
      );
    }

    return $this->board[$row][$col] ?? 0;
  }

  public function countEmpty(): int {
    $count = 0;
    for ($row = 0; $row < $this->height; $row++) {
      for ($col = 0; $col < $this->width; $col++) {
        if ($this->board[$row][$col] === null) {
          $count++;
        }
      }
    }

    return $count;
  }

  public function clear(): self {
    $this->score = 0;
    $this->board = [];
    for ($row = 0; $row < $this->height; $row++) {
      $this->board[$row] = [];
      for ($col = 0; $col < $this->width; $col++) {
        $this->board[$row][$col] = null;
      }
    }

    return $this;
  }

  public function moveUp(): bool {
    $hasMoved = false;
    for ($col = 0; $col < $this->width; $col++) {
      $moveTo = 0;
      $pivot = new Pivot();
      for ($row = 0; $row < $this->height; $row++) {
        // skip empty blocks
        if ($this->board[$row][$col] === null) {
          continue;
        }

        // if there is no pivot value, take the current one as pivot
        if ($pivot->isEmpty()) {
          $pivot->take(
            $this->board[$row][$col],
            $row,
            $col
          );
          $this->board[$row][$col] = null;

          continue;
        }

        // equal values should be merged and moved
        if ($pivot->getVal() === $this->board[$row][$col]) {
          $hasMoved = true;
          $newVal = ($pivot->getVal() + $this->board[$row][$col]);
          $this->emitter->emit(
            'block.merged',
            [
              'up',
              $newVal,
              $moveTo,
              $col
            ]
          );
          $this->board[$moveTo][$col] = $newVal;
          $moveTo++;
          $pivot->clear();
          $this->board[$row][$col] = null;

          continue;
        }

        // move the old pivot and promote the current value to pivot
        if ($pivot->getRow() !== $moveTo) {
          $hasMoved = true;
          $this->emitter->emit(
            'block.moved',
            [
              'up',
              $pivot->getVal(),
              $pivot->getRow(),
              $pivot->getCol(),
              $moveTo,
              $col
            ]
          );
        }

        $this->board[$moveTo][$col] = $pivot->getVal();
        $moveTo++;
        $pivot->take(
          $this->board[$row][$col],
          $row,
          $col
        );
        $this->board[$row][$col] = null;
      }

      // if there is still a pivot after checking all, move it
      if ($pivot->isEmpty() === false) {
        if ($pivot->getRow() !== $moveTo) {
          $hasMoved = true;
          $this->emitter->emit(
            'block.moved',
            [
              'up',
              $pivot->getVal(),
              $pivot->getRow(),
              $pivot->getCol(),
              $moveTo,
              $col
            ]
          );
        }

        $this->board[$moveTo][$col] = $pivot->getVal();
      }
    }

    return $hasMoved;
  }

  public function moveRight(): bool {
    $hasMoved = false;
    for ($row = 0; $row < $this->height; $row++) {
      $moveTo = $this->width - 1;
      $pivot = new Pivot();
      for ($col = $this->width - 1; $col >= 0; $col--) {
        // skip empty blocks
        if ($this->board[$row][$col] === null) {
          continue;
        }

        // if there is no pivot value, take the current one as pivot
        if ($pivot->isEmpty()) {
          $pivot->take(
            $this->board[$row][$col],
            $row,
            $col
          );
          $this->board[$row][$col] = null;

          continue;
        }

        // equal values should be merged and moved
        if ($pivot->getVal() === $this->board[$row][$col]) {
          $hasMoved = true;
          $newVal = ($pivot->getVal() + $this->board[$row][$col]);
          $this->emitter->emit(
            'block.merged',
            [
              'right',
              $newVal,
              $row,
              $moveTo
            ]
          );
          $this->board[$row][$moveTo] = $newVal;
          $moveTo--;
          $pivot->clear();
          $this->board[$row][$col] = null;

          continue;
        }

        // move the old pivot and promote the current value to pivot
        if ($pivot->getCol() !== $moveTo) {
          $hasMoved = true;
          $this->emitter->emit(
            'block.moved',
            [
              'right',
              $pivot->getVal(),
              $pivot->getRow(),
              $pivot->getCol(),
              $row,
              $moveTo
            ]
          );
        }

        $this->board[$row][$moveTo] = $pivot->getVal();
        $moveTo--;
        $pivot->take(
          $this->board[$row][$col],
          $row,
          $col
        );
        $this->board[$row][$col] = null;
      }

      // if there is still a pivot after checking all, move it
      if ($pivot->isEmpty() === false) {
        if ($pivot->getCol() !== $moveTo) {
          $hasMoved = true;
          $this->emitter->emit(
            'block.moved',
            [
              'right',
              $pivot->getVal(),
              $pivot->getRow(),
              $pivot->getCol(),
              $row,
              $moveTo
            ]
          );
        }

        $this->board[$row][$moveTo] = $pivot->getVal();
      }
    }

    return $hasMoved;
  }

  public function moveDown(): bool {
    $hasMoved = false;
    for ($col = 0; $col < $this->width; $col++) {
      $moveTo = $this->height - 1;
      $pivot = new Pivot();
      for ($row = $this->height - 1; $row >= 0; $row--) {
        // skip empty blocks
        if ($this->board[$row][$col] === null) {
          continue;
        }

        // if there is no pivot value, take the current one as pivot
        if ($pivot->isEmpty()) {
          $pivot->take(
            $this->board[$row][$col],
            $row,
            $col
          );
          $this->board[$row][$col] = null;

          continue;
        }

        // equal values should be merged and moved
        if ($pivot->getVal() === $this->board[$row][$col]) {
          $hasMoved = true;
          $newVal = ($pivot->getVal() + $this->board[$row][$col]);
          $this->emitter->emit(
            'block.merged',
            [
              'down',
              $newVal,
              $moveTo,
              $col
            ]
          );
          $this->board[$moveTo][$col] = $newVal;
          $moveTo--;
          $pivot->clear();
          $this->board[$row][$col] = null;

          continue;
        }

        // move the old pivot and promote the current value to pivot
        if ($pivot->getRow() !== $moveTo) {
          $hasMoved = true;
          $this->emitter->emit(
            'block.moved',
            [
              'down',
              $pivot->getVal(),
              $pivot->getRow(),
              $pivot->getCol(),
              $moveTo,
              $col
            ]
          );
        }

        $this->board[$moveTo][$col] = $pivot->getVal();
        $moveTo--;
        $pivot->take(
          $this->board[$row][$col],
          $row,
          $col
        );
        $this->board[$row][$col] = null;
      }

      // if there is still a pivot after checking all, move it
      if ($pivot->isEmpty() === false) {
        if ($pivot->getRow() !== $moveTo) {
          $hasMoved = true;
          $this->emitter->emit(
            'block.moved',
            [
              'down',
              $pivot->getVal(),
              $pivot->getRow(),
              $pivot->getCol(),
              $moveTo,
              $col
            ]
          );
        }

        $this->board[$moveTo][$col] = $pivot->getVal();
      }
    }

    return $hasMoved;
  }

  public function moveLeft(): bool {
    $hasMoved = false;
    for ($row = 0; $row < $this->height; $row++) {
      $moveTo = 0;
      $pivot = new Pivot();
      for ($col = 0; $col < $this->width; $col++) {
        // skip empty blocks
        if ($this->board[$row][$col] === null) {
          continue;
        }

        // if there is no pivot value, take the current one as pivot
        if ($pivot->isEmpty()) {
          $pivot->take(
            $this->board[$row][$col],
            $row,
            $col
          );
          $this->board[$row][$col] = null;

          continue;
        }

        // equal values should be merged and moved
        if ($pivot->getVal() === $this->board[$row][$col]) {
          $hasMoved = true;
          $newVal = ($pivot->getVal() + $this->board[$row][$col]);
          $this->emitter->emit(
            'block.merged',
            [
              'left',
              $newVal,
              $row,
              $moveTo
            ]
          );
          $this->board[$row][$moveTo] = $newVal;
          $moveTo++;
          $pivot->clear();
          $this->board[$row][$col] = null;

          continue;
        }

        // move the old pivot and promote the current value to pivot
        if ($pivot->getCol() !== $moveTo) {
          $hasMoved = true;
          $this->emitter->emit(
            'block.moved',
            [
              'left',
              $pivot->getVal(),
              $pivot->getRow(),
              $pivot->getCol(),
              $row,
              $moveTo
            ]
          );
        }

        $this->board[$row][$moveTo] = $pivot->getVal();
        $moveTo++;
        $pivot->take(
          $this->board[$row][$col],
          $row,
          $col
        );
        $this->board[$row][$col] = null;
      }

      // if there is still a pivot after checking all, move it
      if ($pivot->isEmpty() === false) {
        if ($pivot->getCol() !== $moveTo) {
          $hasMoved = true;
          $this->emitter->emit(
            'block.moved',
            [
              'left',
              $pivot->getVal(),
              $pivot->getRow(),
              $pivot->getCol(),
              $row,
              $moveTo
            ]
          );
        }

        $this->board[$row][$moveTo] = $pivot->getVal();
      }
    }

    return $hasMoved;
  }

  public function toArray(): array {
    return $this->board;
  }

  public function __clone() {
    $this->emitter = clone $this->emitter;
  }
}
