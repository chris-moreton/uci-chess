<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;

$engine = new Engine();

$engine->setEngineLocation('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setModeValue(100000);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setPosition(Engine::STARTPOS);

$move = $engine->getMove('');

echo $move;


