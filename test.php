<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;
use Ryanhs\Chess\Chess;


$rivalSettings = [
    [50, 1076],
    [100, 1104],
    [150, 1171],
    [250, 1274],
    [500, 1305],
    [750, 1311],
    [1000, 1356],
    [1500, 1367],
    [2000, 1441],
    [3500, 1490],
    [5000, 1555],
    [7500, 1638],
    [10000, 1605],
    [12500, 1561],
    [15000, 1666],
    [20000, 1539],
    [25000, 1718],
    [50000, 1807],
    [75000, 2128],
    [100000, 1847],
    [250000, 2015],
    [500000, 2097],
    [600000, 2183],
    [750000, 2076],
    [900000, 2123],
    [1000000, 2197],
];

$cuckooSettings = [
    [1500, 2670],
    [750, 2570],
    [375, 2568],
    [180, 2366],
    [90, 2212],
    [45, 2131],
];

$fluxSettings = [
    [1500, 2370],
    [750, 2351],
    [375, 2109],
    [180, 2036],
    [90, 2006],
    [45, 1925],
];

$tournament = new RoundRobin();
$tournament->setLogFile('log/latest_results.txt');

$engine = new Engine('RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setLogEngineOutput(false);

foreach ($rivalSettings as $setting) {
    $engine->setModeValue($setting[0]);
    $engine->setName('Rival ' . $setting[0]);
    $engine->setElo($setting[1]);
    $tournament->addEngine($engine);
    $engine = clone $engine;
}

$engine = new Engine('cuckoo112.jar uci');
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setMode(Engine::MODE_TIME_MILLIS);
$engine->setName('Cuckoo');
$engine->setLogEngineOutput(false);

foreach ($cuckooSettings as $setting) {
    $engine->setModeValue($setting[0]);
    $engine->setElo($setting[1]);
    $engine->setName('Cuckoo ' . $setting[0]);
    $tournament->addEngine($engine);
    $engine = clone $engine;
}

$engine = new Engine('-Xmx1024M -jar Flux-2.2.1.jar');
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setMode(Engine::MODE_TIME_MILLIS);
$engine->setName('Cuckoo');
$engine->setLogEngineOutput(false);

foreach ($fluxSettings as $setting) {
    $engine->setModeValue($setting[0]);
    $engine->setElo($setting[1]);
    $engine->setName('Flux ' . $setting[0]);
    $tournament->addEngine($engine);
    $engine = clone $engine;
}

$tournament->start();

$tournament->showTable();

$tournament->close();

echo PHP_EOL;
