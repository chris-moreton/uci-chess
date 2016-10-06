# uci-chess

PHP wrapper classes for interfacing with UCI chess engines. 

## Getting started

From the root of your application

    composer require chris-moreton/uci-chess
    
Unless using a framework where autoloading is already taken care of, you'll need to

    include 'vendor/autoload.php';
    
Then, include the classes that you want to use

    use Netsensia\Uci\Engine;
    use Netsensia\Uci\Match;
    
## Search for a move

    $engine = new Engine();
    
    // The location of the chess engine on the local file system
    $engine->setEngineLocation('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
    
    // Set engine parameters
    $engine->setMode(Engine::MODE_NODES);
    $engine->setModeValue(100000);
    
    // Native application or Jar file?
    $engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
    
    // Set the starting position of the match (before any moves have been played)
    $engine->setPosition(Engine::STARTPOS);
    
    // Send the move history and get the move
    $move = $engine->getMove('e2e4 d7d5');
    
    // Important! - remove the process
    $engine->unloadEngine();
    
## Run a match

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
    
    $whiteEngine->unloadEngine();
    $blackEngine->unloadEngine();

## Run a tournament

This is still a work in progress and has limited functionality, but the below code will work

    $tournament = new RoundRobin();
    
    $engine = new Engine('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
    $engine->setMode(Engine::MODE_NODES);
    $engine->setModeValue(100);
    $engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
    $engine->setLogEngineOutput(false);
    $engine->setName('Rival 100');
    
    $tournament->addEngine($engine);
    
    $engine = clone $engine;
    $engine->setModeValue(1000);
    $engine->setName('Rival 1000');
    
    $tournament->addEngine($engine);
    
    $engine = clone $engine;
    $engine->setModeValue(10000);
    $engine->setName('Rival 10000');
    
    $tournament->addEngine($engine);
    
    $tournament->start();
    
    $tournament->showTable();
    
    // Important! - Unload the engine processes
    $tournament->close();
    