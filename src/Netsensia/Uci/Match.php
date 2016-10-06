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
    
    /**
     * @var number
     */
    private $result = null;
    
    /**
     * @var string
     */
    private $fen = null;
    
    /**
     * @var number
     */
    private $mover = Chess::WHITE;
    
    /**
     * @return the $white
     */
    public function getWhite()
    {
        return $this->white;
    }

    /**
     * @return the $black
     */
    public function getBlack()
    {
        return $this->black;
    }

    /**
     * @param \Netsensia\Uci\Engine $white
     */
    public function setWhite($white)
    {
        $this->white = $white;
    }

    /**
     * @param \Netsensia\Uci\Engine $black
     */
    public function setBlack($black)
    {
        $this->black = $black;
    }

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
        
        if (!$this->white instanceof Engine) {
            throw new \Exception('Engine is not valid');
        }
        
        while (!$chess->gameOver()) {

            $engine = $this->mover == Chess::WHITE ? $this->white : $this->black;
            
            $move = $engine->getMove($moves);
        
            $moveArray = [
                'from' => substr($move, 0, 2),
                'to' => substr($move, 2, 2),
                'promotion' => strlen($move) > 4 ? substr($move, 4, 1) : null,
            ];
        
            $chess->move($moveArray);
            
            $this->fen = $chess->fen();
            
            $moves .= $move . ' ';
            
            $this->switchMover();
            
        }
        
        echo PHP_EOL;
        
        $this->result = $chess->inDraw() ? self::DRAW : 
                ($chess->turn() == Chess::WHITE ? self::BLACK_WIN : self::WHITE_WIN);
        
        return [
            'fen' => $this->fen,
            'result' => $this->result,
        ];
    }
    
    /**
     * @return the $result - null if game is not over
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * @return the $fen
     */
    public function getFen()
    {
        return $this->fen;
    }
    
}

