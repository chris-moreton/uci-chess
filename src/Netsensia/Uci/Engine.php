<?php
namespace Netsensia\Uci;

class Engine
{
    const MODE_DEPTH = 1;
    const MODE_TIME_MILLIS = 2;
    const MODE_NODES = 3;
    const MODE_INFINITE = 4;
    
    const APPLICATION_TYPE_JAR = 1;
    const APPLICATION_TYPE_APP = 2;
    
    const STARTPOS = 'startpos';
    
    private $engineLocation;
    private $mode;
    private $modeValue;
    
    private $name;
    
    private $logEngineOutput = true;
    
    private $pipes;
    
    private $position = self::STARTPOS;
    
    private $applicationType = self::APPLICATION_TYPE_APP;
    
    private $errorLog = 'log/error.log';
    private $outputLog = 'log/output.log';
    
    private $process;
    
    private $elo = 1600;
    
    /**
     * @return the $elo
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * @param field_type $elo
     */
    public function setElo($elo)
    {
        $this->elo = $elo;
    }

    /**
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param field_type $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return the $logEngineOutput
     */
    public function getLogEngineOutput()
    {
        return $this->logEngineOutput;
    }

    /**
     * @param boolean $logEngineOutput
     */
    public function setLogEngineOutput($logEngineOutput)
    {
        $this->logEngineOutput = $logEngineOutput;
    }

    public function __construct($engineLocation)
    {
        $this->engineLocation = $engineLocation;    
    }
    
    /**
     * @return the $outputLog
     */
    public function getOutputLog()
    {
        return $this->outputLog;
    }

    /**
     * @param string $outputLog
     */
    public function setOutputLog($outputLog)
    {
        $this->outputLog = $outputLog;
    }

    /**
     * @return the $errorLog
     */
    public function getErrorLog()
    {
        return $this->errorLog;
    }

    /**
     * @param string $errorLog
     */
    public function setErrorLog($errorLog)
    {
        $this->errorLog = $errorLog;
    }

    /**
     * @return the $applicationType
     */
    public function getApplicationType()
    {
        return $this->applicationType;
    }

    /**
     * @param field_type $applicationType
     */
    public function setApplicationType($applicationType)
    {
        $this->applicationType = $applicationType;
    }

    /**
     * @return the $position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return the $mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param field_type $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return the $modeValue
     */
    public function getModeValue()
    {
        return $this->modeValue;
    }

    /**
     * @param field_type $modeValue
     */
    public function setModeValue($modeValue)
    {
        $this->modeValue = $modeValue;
    }
    
    /**
     * Start the engine
     * 
     * @return The engine's process
     */
    public function startEngine()
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("file", $this->errorLog, "a") // stderr is a file to write to
        );
        
        if ($this->applicationType == self::APPLICATION_TYPE_JAR) {
            $command = 'java -jar ';
        } else {
            $command = '';
        }
        
        $command .= $this->engineLocation;
        
        $this->process = proc_open($command, $descriptorspec, $this->pipes);
        
        if (!is_resource($this->pipes[0])) {
            throw new \Exception('Could not start engine');
        }
    }
    
    /**
     * Stop the engine process
     */
    public function unloadEngine()
    {
        if (is_resource($this->process)) {
            
            $this->sendCommand('quit');
            
            for ($i=0; $i<2; $i++) {
                if (is_resource($this->pipes[$i])) {
                    fclose($this->pipes[$i]);
                }
            }
            
            proc_close($this->process);
        }
    }
    
    /**
     * Send a command to the engine
     * 
     * @param string $command
     */
    private function sendCommand($command)
    {
        if (!is_resource($this->pipes[0])) {
            throw new \Exception('Engine has gone!');
        }
        
        $this->log('>> ' . $command);
        
        fwrite($this->pipes[0], $command . PHP_EOL);
    }
    
    /**
     * 
     * @param string $s
     */
    private function log($s)
    {
        if ($this->logEngineOutput) {
            file_put_contents($this->outputLog, $s . PHP_EOL, FILE_APPEND);
        }
    }
    
    /**
     * Send each command in the array
     * 
     * @param array $commands
     */
    private function sendCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->sendCommand($command);
        }
    }
    
    /**
     * Wait for the engine to respond with a command beginning with the give string
     * 
     * @param string $responseStart
     */
    private function waitFor($responseStart)
    {
        if (!is_resource($this->pipes[0])) {
            throw new \Exception('Engine has gone!');
        }
        
        do {
            $output = trim(fgets($this->pipes[1]));
            if ($output != null) {
                $this->log('<< ' . $output);
            }
        } while (strpos($output, $responseStart) !== 0);
        
        return $output;
    }
    
    /**
     * Get a move
     * 
     * @param string $startpos
     * @param string $moveList
     * 
     * @return string $move
     */
    public function getMove($moveList)
    {
        if (!is_resource($this->pipes[0])) {
            $this->startEngine();
        }
        
        switch ($this->mode) {
            case self::MODE_DEPTH : $goCommand = 'depth ' . $this->modeValue; break;
            case self::MODE_NODES : $goCommand = 'nodes ' . $this->modeValue; break;
            case self::MODE_TIME_MILLIS : $goCommand = 'movetime ' . $this->modeValue; break;
            case self::MODE_INFINITE : $goCommand = 'infinite'; break;
        }
        
        $this->sendCommand('uci');
        $this->waitFor('uciok');
        $this->sendCommand('position ' . $this->position . ' moves ' . $moveList);
        $this->sendCommand('go ' . $goCommand);
        $response = $this->waitFor('bestmove');
        $parts = explode(' ', $response);
        
        if (count($parts) != 2) {
            throw new \Exception('Move format was not correct');
        }
        
        $move = $parts[1];
        
        return $move;
    }

    /**
     * Is the engine running?
     * 
     * @return boolean
     */
    public function isEngineRunning()
    {
        return is_resource($this->pipes[0]);
    }
    
    /**
     * Destructor
     */
    function __destruct()
    {
        $this->unloadEngine();
    }
}

