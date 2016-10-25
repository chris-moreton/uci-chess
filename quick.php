<?php

/**
 * A test script that sets up a round robin tournament with three engines configured at
 * multiple perecent strengths. Runs the tournament an infinite number of times, saving
 * the current ELO of each engine in a file after each match.
 */

include 'vendor/autoload.php';

use Netsensia\Uci\Engine;
use Netsensia\Uci\Tournament\RoundRobin;
use Ryanhs\Chess\Chess;

const REPORT_LOCATION = 'engines/report.csv';

$count = 0;
while (true) {
    echo "TOURNAMENT " . ++$count . PHP_EOL;
    run();
}

function run()
{
    $settings = [];
    
    $lines = file(REPORT_LOCATION);

    $tournament = new RoundRobin();
    
    for ($i=1; $i<count($lines); $i++) {
        $parts = str_getcsv($lines[$i]);
        $jarFile = $parts[0];
        $elo = $parts[1];
        
        $engine = new Engine('engines/' . $jarFile);
        $engine->setName($jarFile);
        $engine->setMode(Engine::MODE_TIME_MILLIS);
        $engine->setModeValue(250);
        $engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
        $engine->setLogEngineOutput(true);
        
        $tournament->addEngine($engine);
    }
    
    foreach ($tournament->matches() as $match) {
        
        echo $match->getWhite()->getName() . ' v ' . $match->getBlack()->getName() . PHP_EOL;
        
        $tournament->play($match);
        
        $tableString = $tournament->table();
        
        $tableString = str_replace('Engine,', 'Engine', $tableString);
        echo $tableString;
        
        file_put_contents(REPORT_LOCATION, $tableString);
    }
    
    $tournament->close();
    
    echo PHP_EOL;
}
