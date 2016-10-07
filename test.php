<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;

$engineNodes = [100, 1000, 2000];

$tournament = new RoundRobin();

$engine = new Engine('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setLogEngineOutput(true);

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
