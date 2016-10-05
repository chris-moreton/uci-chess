<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Match;

$whiteEngine = new Engine('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
$blackEngine = new Engine('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');

$whiteEngine->setMode(Engine::MODE_NODES);
$whiteEngine->setModeValue(100);
$whiteEngine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$whiteEngine->setLogEngineOutput(false);

$blackEngine->setMode(Engine::MODE_NODES);
$blackEngine->setModeValue(10000);
$blackEngine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$blackEngine->setLogEngineOutput(false);

$match = new Match($whiteEngine, $blackEngine);

$result = $match->play();

echo $result['fen'] . PHP_EOL;

switch ($result['result']) {
    case Match::DRAW: echo 'Draw';
        break;
    case Match::WHITE_WIN: echo 'White win';
        break;
    case Match::BLACK_WIN: echo 'Black win';
        break;
}

echo PHP_EOL;



