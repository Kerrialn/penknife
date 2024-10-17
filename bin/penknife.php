<?php

declare(strict_types=1);

use KerrialNewham\Penknife\Command\CodingStandardsCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$application = new Application();
$application->add(new CodingStandardsCommand());
$application->run();