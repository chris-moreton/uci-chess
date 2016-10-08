<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;

$engineNodes = [50, 100, 150, 250, 500, 750, 1000, 1500, 2000, 3500, 5000, 7500, 10000, 12500, 15000, 20000, 25000, 50000, 75000, 100000, 250000, 500000, 600000, 750000, 900000, 1000000];

$tournament = new RoundRobin();

$engine = new Engine('RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setLogEngineOutput(false);
$engine->setElo(1600);

foreach ($engineNodes as $nodes) {
    $engine->setModeValue($nodes);
    $engine->setName('Rival ' . $nodes);
    $tournament->addEngine($engine);
    $engine = clone $engine;
}

$engine = new Engine('cuckoo112.jar uci');
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setMode(Engine::MODE_TIME_MILLIS);
$engine->setModeValue(1500);
$engine->setElo(2590);
$engine->setName('Cuckoo');
$tournament->addEngine($engine);

$tournament->start();

$tournament->showTable();

$tournament->close();

echo PHP_EOL;
