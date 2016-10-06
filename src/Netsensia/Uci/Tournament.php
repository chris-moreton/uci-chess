<?php
namespace Netsensia\Uci;

use Netsensia\Uci\Engine;

abstract class Tournament
{
    protected $engines = [];

    abstract public function showTable();
    abstract public function start();
    
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
        foreach ($this->engines as $engine) {
            $engine->unloadEngine();
        }
    }
}

