<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;
use Ryanhs\Chess\Chess;

$lines = file('reports/latest_results.txt');

$cuckooSettings = [];
$rivalSettings = [];
$fluxSettings = [];

$rivalMillisToSearch1000000Nodes = 1500;

for ($i=2; $i<count($lines)-1; $i++) {
    $lines[$i] = preg_replace('!\s+!', ' ', $lines[$i]);
    $parts = explode(' ', $lines[$i]);
    $name = $parts[0] . ' ' . $parts[1];
    switch ($parts[0]) {
        case 'Cuckoo' :
            $percent = str_replace('%', '', $parts[1]);
            $millis = ($rivalMillisToSearch1000000Nodes / 100) * $percent;
            $cuckooSettings[] = [$millis, $parts[2], $name];
            break;
        case 'Flux' :
            $percent = str_replace('%', '', $parts[1]);
            $millis = ($rivalMillisToSearch1000000Nodes / 100) * $percent;
            $fluxSettings[] = [$millis, $parts[2], $name];
            break;
        case 'Rival' :
            $rivalSettings[] = [$parts[1], $parts[2], $name];
            break;            
    }
}

$tournament = new RoundRobin();
$tournament->setLogFile('log/tournament.log');
$tournament->setResultsFile('reports/latest_results.txt');

$engine = new Engine('RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setLogEngineOutput(false);

foreach ($rivalSettings as $setting) {
    $engine->setModeValue($setting[0]);
    $engine->setName($setting[2]);
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
    $engine->setName($setting[2]);
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
    $engine->setName($setting[2]);
    $tournament->addEngine($engine);
    $engine = clone $engine;
}

$tournament->start();

$tournament->close();

echo PHP_EOL;
