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
    
    if (!$engine->isEngineRunning()) {
        echo 123; die;
    }
    
    $moveArray = [
        'from' => substr($move, 0, 2),
        'to' => substr($move, 2, 2),
        'promotion' => strlen($move) > 4 ? substr($move, 4, 1) : null,
    ];
    
    $chess->move($moveArray);
    $moves .= $move . ' ';
}

echo $chess->fen() . PHP_EOL;

if ($chess->inDraw()) {
    echo "Game drawn";
} else {
    if ($chess->turn() == Chess::WHITE) {
        echo "Black wins";
    } else {
        echo "White wins";
    }
}

echo PHP_EOL;



