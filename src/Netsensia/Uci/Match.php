<?php
namespace Netsensia\Uci;

use Ryanhs\Chess\Chess;

class Match
{
    const DRAW = 0;
    const WHITE_WIN = 1;
    const BLACK_WIN = 2;
    
    /**
     * @var boolean
     */
    private $output = true;
    
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
     * @return the $output
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param boolean $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return Engine $white
     */
    public function getWhite()
    {
        return $this->white;
    }

    /**
     * @return Engine $black
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
        
        $moveCount = 0;
        
        while (!$chess->gameOver() && $moveCount < 500) {

            $engine = $this->mover == Chess::WHITE ? $this->white : $this->black;
            
            $move = $engine->getMove($moves);
            
            if ($this->output) {
                echo $move . ' ';
            }
        
            $moveArray = [
                'from' => substr($move, 0, 2),
                'to' => substr($move, 2, 2),
                'promotion' => strlen($move) > 4 ? strtolower(substr($move, 4, 1)) : null,
            ];
        
            if ($chess->move($moveArray) === null) {
                $this->result = $this->mover == Chess::WHITE ? self::BLACK_WIN : self::WHITE_WIN;
                return [
                    'fen' => $this->fen,
                    'result' => $this->result,
                    'reason' => 'illegal-move',
                ]; 
            }
            
            $this->fen = $chess->fen();
            
            $moves .= $move . ' ';
            
            $this->switchMover();
            $moveCount ++;
            
        }
        
        if ($this->output) {
            echo PHP_EOL;
        }
        
        $this->white->unloadEngine();
        $this->black->unloadEngine();
        
        echo PHP_EOL;
        
        // Not detecting all threefold repititions for some reason, hacky fix.
        if ($moveCount == 500) {
            $this->result = self::DRAW;
        } else { 
            $this->result = $chess->inDraw() ? self::DRAW : 
                    ($chess->turn() == Chess::WHITE ? self::BLACK_WIN : self::WHITE_WIN);
        }
        
        return [
            'fen' => $this->fen,
            'result' => $this->result,
            'reason' => 'game-over',
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

