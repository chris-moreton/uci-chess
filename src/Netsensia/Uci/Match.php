<?php
namespace Netsensia\Uci;

class Match
{
    /**
     * @var Engine
     */
    private $white;
    
    /**
     * @var Engine
     */
    private $black;
    
    /**
     * @return the $white
     */
    public function getWhite()
    {
        return $this->white;
    }

    /**
     * @param \Netsensia\Uci\Engine $white
     */
    public function setWhite($white)
    {
        $this->white = $white;
    }

    /**
     * @return the $black
     */
    public function getBlack()
    {
        return $this->black;
    }

    /**
     * @param \Netsensia\Uci\Engine $black
     */
    public function setBlack($black)
    {
        $this->black = $black;
    }

    
    
}

