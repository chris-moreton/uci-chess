<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;

$engineNodes = [100, 250, 500, 750, 1000, 2000, 5000, 10000, 15000, 25000, 50000, 75000, 100000, 500000, 1000000];

$tournament = new RoundRobin();

$engine = new Engine('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setLogEngineOutput(true);
$engine->setElo(1600);

foreach ($engineNodes as $nodes) {
    $engine->setModeValue($nodes);
    $engine->setName('Rival ' . $nodes);
    $tournament->addEngine($engine);
    $engine = clone $engine;
}

$tournament->start();

$tournament->showTable();

$tournament->close();

echo PHP_EOL;
