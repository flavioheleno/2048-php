<?php
declare(strict_types = 1);

namespace TwentyFourtyEight\Board;

use InvalidArgumentException;

class Pivot {
  private int $val = -1;
  private int $row = -1;
  private int $col = -1;

  public function isEmpty(): bool {
    return $this->val === -1;
  }

  public function getVal(): int {
    return $this->val;
  }

  public function getRow(): int {
    return $this->row;
  }

  public function getCol(): int {
    return $this->col;
  }

  public function take(int $val, int $row, int $col): void {
    if ($val < 0) {
      throw new InvalidArgumentException('$val argument must be positive');
    }

    if ($row < 0) {
      throw new InvalidArgumentException('$row argument must be positive');
    }

    if ($col < 0) {
      throw new InvalidArgumentException('$col argument must be positive');
    }

    $this->val = $val;
    $this->row = $row;
    $this->col = $col;
  }

  public function clear(): void {
    $this->val = -1;
    $this->row = -1;
    $this->col = -1;
  }
}
