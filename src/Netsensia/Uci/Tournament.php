<?php
namespace Netsensia\Uci;

use Netsensia\Uci\Engine;

abstract class Tournament
{
    protected $engines = [];
    protected $output = true;
    
    /**
     * @var string
     */
    protected $logFile = null;

    /**
     * @return the $logFile
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param string $logFile
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * @return the $output
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Should the tournament output info to the console?
     * 
     * @param boolean $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

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
        for ($i=0; $i<count($this->engines); $i++) {
            $this->engines[$i]['engine']->unloadEngine();
        }
    }
}

