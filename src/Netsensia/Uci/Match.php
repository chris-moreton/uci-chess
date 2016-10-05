<?php
namespace Netsensia\Uci;

use Ryanhs\Chess\Chess;

class Match
{
    const DRAW = 0;
    const WHITE_WIN = 1;
    const BLACK_WIN = 2;
    
    /**
     * @var Engine
     */
    private $white;
    
    /**
     * @var Engine
     */
    private $black;
    
    private $mover = Chess::WHITE;
    
    public function __construct(Engine $white, Engine $black)
    {
        $this->white = $white;
        $this->black = $black;
    }

    /**
     * 
     * @param Engine $engine
     */
    private function setEngineParams(Engine $engine)
    {
        $engine->setPosition(Engine::STARTPOS);
    }
    
    /**
     * Switch the mover
     */
    private function switchMover()
    {
        if ($this->mover == Chess::WHITE) {
            $this->mover = Chess::BLACK;
        } else {
            $this->mover = Chess::WHITE;
        }
    }
    
    /**
     * 
     * @return string[]
     */
    public function play()
    {
        $chess = new Chess();

        $moves = '';
        
        $this->setEngineParams($this->white);
        $this->setEngineParams($this->black);
        
        while (!$chess->gameOver()) {

            $engine = $this->mover == Chess::WHITE ? $this->white : $this->black;
            
            $move = $engine->getMove($moves);
        
            $moveArray = [
                'from' => substr($move, 0, 2),
                'to' => substr($move, 2, 2),
                'promotion' => strlen($move) > 4 ? substr($move, 4, 1) : null,
            ];
        
            $chess->move($moveArray);
            $moves .= $move . ' ';
            
            $this->switchMover();
        }
        
        return [
            'fen' => $chess->fen(),
            'result' => 
                $chess->inDraw() ? self::DRAW : 
                ($chess->turn() == Chess::WHITE ? self::BLACK_WIN : self::WHITE_WIN),
        ];
    }
    
}

