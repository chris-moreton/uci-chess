<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;
use Ryanhs\Chess\Chess;

const REPORT_LOCATION = '/Users/Chris/Dropbox/Dashboard/UCITournament.csv';
$lines = file(REPORT_LOCATION);

$cuckooSettings = [];
$rivalSettings = [];
$fluxSettings = [];

echo 'Determining Rival Speed...' . PHP_EOL;
$engine = new Engine('RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setLogEngineOutput(false);
$engine->setModeValue(1000000);
$start = microtime(true);
$engine->getMove();
$engine->unloadEngine();
$time = microtime(true) - $start;

$rivalMillisToSearch1000000Nodes = ceil($time * 1000);

echo 'Milliseconds to search when timed engines are 100% = ' . $rivalMillisToSearch1000000Nodes . PHP_EOL;

for ($i=1; $i<count($lines); $i++) {
    $parts = str_getcsv($lines[$i]);
    $name = $parts[0];
    $elo = $parts[1];
    $nameSplit = explode(' ', $name);
    $modeValue = $nameSplit[1];
    switch ($nameSplit[0]) {
        case 'Cuckoo' :
            $percent = str_replace('%', '', $modeValue);
            $millis = ceil(($rivalMillisToSearch1000000Nodes / 100) * $percent);
            $cuckooSettings[] = [$millis, $elo, $name];
            break;
        case 'Flux' :
            $percent = str_replace('%', '', $modeValue);
            $millis = ceil(($rivalMillisToSearch1000000Nodes / 100) * $percent);
            $fluxSettings[] = [$millis, $elo, $name];
            break;
        case 'Rival' :
            $percent = str_replace('%', '', $modeValue);
            $millis = ceil(($rivalMillisToSearch1000000Nodes / 100) * $percent);
            $rivalSettings[] = [$millis, $elo, $name];
            break;            
    }
}

$tournament = new RoundRobin();
$tournament->setLogFile('log/tournament.log');
$tournament->setResultsFile(REPORT_LOCATION);

$engine = new Engine('RivalChess.jar');
$engine->setMode(Engine::MODE_TIME_MILLIS);
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
