<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;

$tournament = new RoundRobin();

$engine = new Engine('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setModeValue(100);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setLogEngineOutput(false);
$engine->setName('Rival 100');

$tournament->addEngine($engine);

$engine = clone $engine;
$engine->setModeValue(1000);
$engine->setName('Rival 1000');

$tournament->addEngine($engine);

$engine = clone $engine;
$engine->setModeValue(10000);
$engine->setName('Rival 10000');

$tournament->addEngine($engine);

$tournament->start();

$tournament->showTable();

$tournament->close();

echo PHP_EOL;



