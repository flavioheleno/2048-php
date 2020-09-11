<?php
declare(strict_types = 1);

namespace TwentyFourtyEightTest\Board;

use Evenement\EventEmitter;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TwentyFourtyEight\Board\Board;

class BoardTest extends TestCase {
  private $emitter;

  public function setUp(): void {
    $this->emitter = $this->createMock(EventEmitter::class);
  }

  public function testInvalidWidth() {
    $this->expectException(
      InvalidArgumentException::class,
      'Board width cannot be smaller than 1'
    );
    new Board($this->emitter, 0);
  }

  public function testInvalidHeight() {
    $this->expectException(
      InvalidArgumentException::class,
      'Board height cannot be smaller than 1'
    );
    new Board($this->emitter, 1, 0);
  }

  public function testLoad() {
    $boardMap = [
      [1, 8,  9, 13],
      [2, 7, 10, 14],
      [3, 6, 11, 15],
      [4, 5, 12, 16]
    ];
    $board = new Board($this->emitter);
    $this->assertEquals(
      $board->toArray(),
      [
        [null, null, null, null],
        [null, null, null, null],
        [null, null, null, null],
        [null, null, null, null]
      ]
    );
    $board->load($boardMap);
    $this->assertEquals(
      $board->toArray(),
      $boardMap
    );
  }

  public function testSizeGetters() {
    $board = new Board($this->emitter);
    $this->assertSame($board->getWidth(), 4);
    $this->assertSame($board->getHeight(), 4);
  }

  public function testEmptyAt() {
    $board = new Board($this->emitter);
    $board->load(
      [
        [   1,    8,    9, null],
        [   2, null,   10,   14],
        [null,    6,   11,   15],
        [   4,    5, null,   16]
      ]
    );

    // first row
    $this->assertFalse($board->emptyAt(0, 0));
    $this->assertFalse($board->emptyAt(0, 1));
    $this->assertFalse($board->emptyAt(0, 2));
    $this->assertTrue($board->emptyAt(0, 3));
    // second row
    $this->assertFalse($board->emptyAt(1, 0));
    $this->assertTrue($board->emptyAt(1, 1));
    $this->assertFalse($board->emptyAt(1, 2));
    $this->assertFalse($board->emptyAt(1, 3));
    // third row
    $this->assertTrue($board->emptyAt(2, 0));
    $this->assertFalse($board->emptyAt(2, 1));
    $this->assertFalse($board->emptyAt(2, 2));
    $this->assertFalse($board->emptyAt(2, 3));
    // fourth row
    $this->assertFalse($board->emptyAt(3, 0));
    $this->assertFalse($board->emptyAt(3, 1));
    $this->assertTrue($board->emptyAt(3, 2));
    $this->assertFalse($board->emptyAt(3, 3));
  }

  public function testValueAt() {
    $board = new Board($this->emitter);
    $board->load(
      [
        [   1,    8,    9, null],
        [   2, null,   10,   14],
        [null,    6,   11,   15],
        [   4,    5, null,   16]
      ]
    );

    // first row
    $this->assertSame($board->valueAt(0, 0), 1);
    $this->assertSame($board->valueAt(0, 1), 8);
    $this->assertSame($board->valueAt(0, 2), 9);
    $this->assertSame($board->valueAt(0, 3), 0);
    // second row
    $this->assertSame($board->valueAt(1, 0), 2);
    $this->assertSame($board->valueAt(1, 1), 0);
    $this->assertSame($board->valueAt(1, 2), 10);
    $this->assertSame($board->valueAt(1, 3), 14);
    // third row
    $this->assertSame($board->valueAt(2, 0), 0);
    $this->assertSame($board->valueAt(2, 1), 6);
    $this->assertSame($board->valueAt(2, 2), 11);
    $this->assertSame($board->valueAt(2, 3), 15);
    // fourth row
    $this->assertSame($board->valueAt(3, 0), 4);
    $this->assertSame($board->valueAt(3, 1), 5);
    $this->assertSame($board->valueAt(3, 2), 0);
    $this->assertSame($board->valueAt(3, 3), 16);
  }

  public function testCountEmpty() {
    $board = new Board($this->emitter);
    $board->load(
      [
        [   1,    8,    9, null],
        [   2, null,   10,   14],
        [null,    6,   11,   15],
        [   4,    5, null,   16]
      ]
    );
    $this->assertSame($board->countEmpty(), 4);
  }

  public function testMoveUp() {
    $boardMap = [
      [null, null, null,    2],
      [null,    2,    2,    2],
      [   2, null,    2,    2],
      [null,    2,    2,    2]
    ];
    $board = new Board($this->emitter);
    $board->load($boardMap);
    $this->assertTrue($board->moveUp());
    $this->assertEquals(
      $board->toArray(),
      [
        [   2,    4,    4,    4],
        [null, null,    2,    4],
        [null, null, null, null],
        [null, null, null, null]
      ]
    );
    $this->assertTrue($board->moveUp());
    $this->assertEquals(
      $board->toArray(),
      [
        [   2,    4,    4,    8],
        [null, null,    2, null],
        [null, null, null, null],
        [null, null, null, null]
      ]
    );
    $this->assertFalse($board->moveUp());
    $this->assertEquals(
      $board->toArray(),
      [
        [   2,    4,    4,    8],
        [null, null,    2, null],
        [null, null, null, null],
        [null, null, null, null]
      ]
    );
  }

  public function testMoveRight() {
    $boardMap = [
      [   2, null, null, null],
      [   2,    2,    2, null],
      [   2,    2, null,    2],
      [   2,    2,    2,    2]
    ];
    $board = new Board($this->emitter);
    $board->load($boardMap);
    $this->assertTrue($board->moveRight());
    $this->assertEquals(
      $board->toArray(),
      [
        [null, null, null,   2],
        [null, null,    2,   4],
        [null, null,    2,   4],
        [null, null,    4,   4]
      ]
    );
    $this->assertTrue($board->moveRight());
    $this->assertEquals(
      $board->toArray(),
      [
        [null, null, null,   2],
        [null, null,    2,   4],
        [null, null,    2,   4],
        [null, null, null,   8]
      ]
    );
    $this->assertFalse($board->moveRight());
    $this->assertEquals(
      $board->toArray(),
      [
        [null, null, null,   2],
        [null, null,    2,   4],
        [null, null,    2,   4],
        [null, null, null,   8]
      ]
    );
  }

  public function testMoveDown() {
    $boardMap = [
      [null, null, null,    2],
      [null,    2,    2,    2],
      [   2, null,    2,    2],
      [null,    2,    2,    2]
    ];
    $board = new Board($this->emitter);
    $board->load($boardMap);
    $this->assertTrue($board->moveDown());
    $this->assertEquals(
      $board->toArray(),
      [
        [null, null, null, null],
        [null, null, null, null],
        [null, null,    2,    4],
        [   2,    4,    4,    4]
      ]
    );
    $this->assertTrue($board->moveDown());
    $this->assertEquals(
      $board->toArray(),
      [
        [null, null, null, null],
        [null, null, null, null],
        [null, null,    2, null],
        [   2,    4,    4,    8]
      ]
    );
    $this->assertFalse($board->moveDown());
    $this->assertEquals(
      $board->toArray(),
      [
        [null, null, null, null],
        [null, null, null, null],
        [null, null,    2, null],
        [   2,    4,    4,    8]
      ]
    );
  }

  public function testMoveLeft() {
    $boardMap = [
      [   2, null, null, null],
      [   2,    2,    2, null],
      [   2,    2, null,    2],
      [   2,    2,    2,    2]
    ];
    $board = new Board($this->emitter);
    $board->load($boardMap);
    $this->assertTrue($board->moveLeft());
    $this->assertEquals(
      $board->toArray(),
      [
        [   2, null, null, null],
        [   4,    2, null, null],
        [   4,    2, null, null],
        [   4,    4, null, null]
      ]
    );
    $this->assertTrue($board->moveLeft());
    $this->assertEquals(
      $board->toArray(),
      [
        [   2, null, null, null],
        [   4,    2, null, null],
        [   4,    2, null, null],
        [   8, null, null, null]
      ]
    );
    $this->assertFalse($board->moveLeft());
    $this->assertEquals(
      $board->toArray(),
      [
        [   2, null, null, null],
        [   4,    2, null, null],
        [   4,    2, null, null],
        [   8, null, null, null]
      ]
    );
  }

  public function testToArray() {
    $board = new Board($this->emitter);
    $this->assertEquals(
      $board->toArray(),
      [
        [null, null, null, null],
        [null, null, null, null],
        [null, null, null, null],
        [null, null, null, null]
      ]
    );
  }
}
