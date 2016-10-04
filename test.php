<?php
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Ryanhs\Chess\Chess;

$engine = new Engine();

$engine->setEngineLocation('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
$engine->setMode(Engine::MODE_NODES);
$engine->setModeValue(1000);
$engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
$engine->setPosition(Engine::STARTPOS);

$moves = '';

$chess = new Chess();

while (!$chess->gameOver()) {
    
    $move = $engine->getMove($moves);
    
    $moveArray = [
        'from' => substr($move, 0, 2),
        'to' => substr($move, 2, 2),
        'promotion' => strlen($move) > 4 ? substr($move, 4, 1) : null,
    ];
    
    echo $move . PHP_EOL;
    
    $chess->move($moveArray);
    $moves .= $move . ' ';
}

echo 'GAME OVER!';
echo PHP_EOL;



