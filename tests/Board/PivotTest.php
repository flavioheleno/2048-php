<?php
declare(strict_types = 1);

namespace TwentyFourtyEightTest\Board;

use TwentyFourtyEight\Board\Pivot;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;

class PivotTest extends TestCase {
  public function testNewEmptyPivot() {
    $pivot = new Pivot();
    $this->assertTrue($pivot->isEmpty());
    $this->assertSame($pivot->getVal(), -1);
    $this->assertSame($pivot->getRow(), -1);
    $this->assertSame($pivot->getCol(), -1);
  }

  public function testInvalidVal() {
    $this->expectException(
      InvalidArgumentException::class,
      '$val argument must be positive'
    );
    $pivot = new Pivot();
    $pivot->take(-1, 0, 0);
  }

  public function testInvalidRow() {
    $this->expectException(
      InvalidArgumentException::class,
      '$row argument must be positive'
    );
    $pivot = new Pivot();
    $pivot->take(0, -1, 0);
  }

  public function testInvalidCol() {
    $this->expectException(
      InvalidArgumentException::class,
      '$col argument must be positive'
    );
    $pivot = new Pivot();
    $pivot->take(0, 0, -1);
  }

  public function testGetters() {
    $pivot = new Pivot();
    $pivot->take(1, 2, 3);
    $this->assertFalse($pivot->isEmpty());
    $this->assertSame($pivot->getVal(), 1);
    $this->assertSame($pivot->getRow(), 2);
    $this->assertSame($pivot->getCol(), 3);
    $pivot->clear();
    $this->assertTrue($pivot->isEmpty());
    $this->assertSame($pivot->getVal(), -1);
    $this->assertSame($pivot->getRow(), -1);
    $this->assertSame($pivot->getCol(), -1);
  }
}
