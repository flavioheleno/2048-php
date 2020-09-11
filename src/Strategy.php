<?php
declare(strict_types = 1);

namespace TwentyFourtyEight;

use RuntimeException;
use Tree\Node\Node;

final class Strategy {
  private static function buildTree(Node &$root, Game $game, int $depth): void {
    if ($depth === 0) {
      return;
    }

    $root
      ->addChild(new Node(['up', (clone $game)->moveUp()]))
      ->addChild(new Node(['right', (clone $game)->moveRight()]))
      ->addChild(new Node(['down', (clone $game)->moveDown()]))
      ->addChild(new Node(['left', (clone $game)->moveLeft()]));

    $depth--;
    foreach ($root->getChildren() as $child) {
      self::buildTree($child, $child->getValue()[1], $depth);
    }
  }

  public static function probe(Game $game, callable $score, int $depth = 0): string {
//     $tree = new Node('root');
//     self::buildTree($tree, $game, $depth);

// exit;

    $scores = [
      'up'    => 0.0,
      'right' => 0.0,
      'down'  => 0.0,
      'left'  => 0.0
    ];
    $probes = [
      'up'    => (clone $game)->moveUp(),
      'right' => (clone $game)->moveRight(),
      'down'  => (clone $game)->moveDown(),
      'left'  => (clone $game)->moveLeft()
    ];

    foreach ($probes as $movement => $probe) {
      if ($probe->isLastMoveValid() === false) {
        unset($probes[$movement]);

        continue;
      }
    }

    if (count($probes) === 0) {
      throw new RuntimeException('No moves left');
    }

    foreach ($probes as $movement => $probe) {
      $scores[$movement] = $score($probe);
    }

    $decision = '';
    $bestScore = 0;
    array_walk(
      $scores,
      function ($value, $key) use (&$decision, &$bestScore) {
        if ($value > $bestScore) {
          $decision = $key;
          $bestScore = $value;
        }
      }
    );

    return $decision;
  }
}
