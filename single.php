<?php 
include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Match;

$whiteWins = 0;
$blackWins = 0;
$player1Wins = 0;
$player2Wins = 0;
$draws = 0;
$count = 0;

while (true) {
    $count ++;
    
    $engine = new Engine('/Applications/Deep\ Junior\ Yokohama/Deep\ Junior\ Yokohama');
    $engine->setApplicationType(Engine::APPLICATION_TYPE_APP);
    $engine->setMode(Engine::MODE_TIME_MILLIS);
    $engine->setName('Deep Junior');
    $engine->setLogEngineOutput(false);
    
    $engine->setModeValue(1250);
    $engine->setElo(3000);
    $engine->setName('Deep Junior');
    $engine->setRestrictToElo(2659);
    $engine->setMaxThreads(null);
    
    $white = $engine;
    
    $black = clone $white;
    $black->setMaxThreads(null);
    
    if ($count % 2 == 0) {
        $temp = $white;
        $white = $black;
        $black = $temp;
    }
    
    $match = new Match($white, $black);
    $match->play();
    
    switch ($match->getResult()) {
        case Match::WHITE_WIN:
            $whiteWins++;
            if ($count % 2 == 0) $player2Wins ++; else $player1Wins ++;
            break;
        case Match::BLACK_WIN:
            if ($count % 2 == 1) $player2Wins ++; else $player1Wins ++;
            $blackWins++;
            break;
        case Match::DRAW:
            $draws++;
            break;
        default: die('Result error');
    }

    echo 'White = ' . $whiteWins . ' Black = ' . $blackWins . ' Draws = ' . $draws . PHP_EOL;
    echo 'Player 1 = ' . $player1Wins . ' Player 2 = ' . $player2Wins . ' Draws = ' . $draws . PHP_EOL;
    
    $white->unloadEngine();
    $black->unloadEngine();
    
}
