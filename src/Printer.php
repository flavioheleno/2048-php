<?php
declare(strict_types = 1);

namespace TwentyFourtyEight;

use TwentyFourtyEight\Board\Board;

final class Printer {
  public static function toArray(Board $board): array {
    $lines = [];
    $width = $board->getWidth();
    $height = $board->getHeight();
    for ($row = 0; $row < $width; $row++) {
      $line = '';
      for ($col = 0; $col < $height; $col++) {
        $val = $board->valueAt($row, $col);
        if ($val === 0) {
          $line .= '[    ]';

          continue;
        }

        $line .= sprintf('[% 4d]', $board->valueAt($row, $col));
      }

      $lines[] = $line;
    }

    return $lines;
  }

  public static function toString(Board $board): string {
    return implode(PHP_EOL, self::toArray($board));
  }
}
