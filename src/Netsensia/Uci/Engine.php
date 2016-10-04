<?php
namespace Netsensia\Uci;

class Engine
{
    const MODE_DEPTH = 1;
    const MODE_TIME_MILLIS = 2;
    const MODE_NODES = 3;
    
    private $engineLocation;
    private $mode;
    private $modeValue;
    
    /**
     * @return the $engineLocation
     */
    public function getEngineLocation()
    {
        return $this->engineLocation;
    }

    /**
     * @param field_type $engineLocation
     */
    public function setEngineLocation($engineLocation)
    {
        $this->engineLocation = $engineLocation;
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

}

