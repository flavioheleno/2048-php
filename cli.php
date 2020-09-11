#!/usr/bin/env php
<?php
declare(strict_types = 1);

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF8');
define('__ROOT__', __DIR__);

require __DIR__.'/vendor/autoload.php';

use TwentyFourtyEight\Cli;
use SebastianBergmann\Version;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;


$version = new Version('0.1', __DIR__);

$application = new Application('2048-php', $version->getVersion());
$commandLoader = new FactoryCommandLoader(
  [
    Cli::getDefaultName() => function (): Cli {
      return new Cli;
    }
  ]
);
$application->setCommandLoader($commandLoader);
$application->run();
