# uci-chess

PHP wrapper classes for interfacing with UCI chess engines. 

## Getting started

From the root of your application

    composer require chris-moreton/uci-chess
    
Unless using a framework where autoloading is already taken care of, you'll need to

    include 'vendor/autoload.php';
    
Then, include the classes that you want to use, e.g.

    use Netsensia\Uci\Engine;
    use Netsensia\Uci\Match;
    use Netsensia\Uci\Tournament\RoundRobin;
    
## Search for a move

    $engine = new Engine();

    // Native application or Jar file?
    $engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
        
    // The location of the chess engine on the local file system
    // You can download my engine, RivalChess from https://github.com/chris-moreton/rival-chess-android-engine/tree/master/dist
    // If the engine requires any parameters, simply add them after the path
    $engine->setEngineLocation('/path/to/engine.jar [params]');
    
    // Set engine parameters
    $engine->setMode(Engine::MODE_NODES);
    $engine->setModeValue(100000);
    
    // Set the starting position of the match (before any moves have been played)
    $engine->setPosition(Engine::STARTPOS);
    
    // Send the move history and get the move
    $move = $engine->getMove('e2e4 d7d5');
    
    // Important! - remove the process
    $engine->unloadEngine();
    
## Run a match

    $whiteEngine = new Engine('/path/to/engine1.jar');
    
    // No reason why you can't use the same engine if you want to test against different parameters
    $blackEngine = new Engine('/path/to/engine2.jar');
    
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
    
    $whiteEngine->unloadEngine();
    $blackEngine->unloadEngine();

## Run a tournament

    $tournament = new RoundRobin();
 
    // for each engine...   
    $engine = new Engine('/path/to/engine1.jar');
    $engine->setMode(Engine::MODE_NODES);
    $engine->setModeValue(100);
    $engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
    $engine->setLogEngineOutput(false);
    $engine->setName('Engine 1');
    $tournament->addEngine($engine);
    
    ...
    
    foreach ($tournament->matches() as $match) {
        echo $match->getWhite()->getName() . ' v ' . $match->getBlack()->getName() . PHP_EOL;
        $tournament->play($match);
        $echo $tournament->table();
    }
    
    $tournament->showTable();
    
    $tournament->close();
    