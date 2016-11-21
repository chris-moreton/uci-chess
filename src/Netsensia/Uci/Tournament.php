<?php
namespace Netsensia\Uci;

use Netsensia\Uci\Engine;

abstract class Tournament
{
    protected $engines = [];
    protected $numberOfRuns = 1;

    abstract public function table();
    abstract public function matches();
    
    /**
     * @return member variable $numberOfRuns
     */
    public function getNumberOfRuns()
    {
        return $this->numberOfRuns;
    }

    /**
     * @param number $numberOfRuns
     */
    public function setNumberOfRuns($numberOfRuns)
    {
        $this->numberOfRuns = $numberOfRuns;
    }

    /**
     * @param Engine $engine
     */
    public function addEngine(Engine $engine)
    {
        $this->engines[] = [
            'engine' => $engine,
            'matches' => [],
            'results' => ['win' => 0, 'loss' => 0, 'draw' => 0],
            'score' => 0,
        ];
    }
    
    /**
     * Unload all the engines
     */
    public function close()
    {
        for ($i=0; $i<count($this->engines); $i++) {
            $this->engines[$i]['engine']->unloadEngine();
        }
    }
}

