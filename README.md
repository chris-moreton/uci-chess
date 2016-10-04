# uci-tournament

A Composer module for running UCI chess engines, either standalone, in a match, or in a tournament.

    $engine = new Engine();
    
    $engine->setEngineLocation('/Users/Chris/git/chess/rival-chess-android-engine/dist/RivalChess.jar');
    $engine->setMode(Engine::MODE_NODES);
    $engine->setModeValue(100000);
    $engine->setApplicationType(Engine::APPLICATION_TYPE_JAR);
    $engine->setPosition(Engine::STARTPOS);
    
    $move = $engine->getMove('e2e4 d7d5');
