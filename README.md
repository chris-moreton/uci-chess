# uci-chess

PHP wrapper classes for interfacing with UCI chess engines. 

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
