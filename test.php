<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;
use Ryanhs\Chess\Chess;

$engineNodes = [50, 100, 150, 250, 500, 750, 1000, 1500, 2000, 3500, 5000, 7500, 10000, 12500, 15000, 20000, 25000, 50000, 75000, 100000, 250000, 500000, 600000, 750000, 900000, 1000000];
$cuckooSettings = [
    [1500, 2600],
    [750, 2550],
    [375, 2500],
    [180, 2450],
    [90, 2400],
    [45, 2350],
];

$tournament = new RoundRobin();

$engine = new Engine('RivalChess.jar');
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

$engine = new Engine('cuckoo112.jar uci');
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setMode(Engine::MODE_TIME_MILLIS);
$engine->setName('Cuckoo');
$engine->setLogEngineOutput(true);

foreach ($cuckooSettings as $setting) {
    $engine->setModeValue($setting[0]);
    $engine->setElo($setting[1]);
    $engine->setName('Cuckoo ' . $setting[0]);
    $tournament->addEngine($engine);
    $engine = clone $engine;
}

$tournament->start();

$tournament->showTable();

$tournament->close();

echo PHP_EOL;
